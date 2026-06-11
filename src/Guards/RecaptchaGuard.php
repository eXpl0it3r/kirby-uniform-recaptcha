<?php

namespace Uniform\Guards;

use Kirby\Http\Remote;
use Throwable;
use Uniform\Exceptions\Exception;

/**
 * Uniform guard using Google reCAPTCHA v3
 */
class RecaptchaGuard extends Guard
{
    /**
     * reCAPTCHA HTML input field name
     *
     * @var string
     */
    const FieldName = 'g-recaptcha-response';

    /**
     * reCAPTCHA action name used
     *
     * @var string
     */
    const ActionName = 'UniformAction';

    /**
     * URL for the reCAPTCHA verification
     *
     * @var string
     */
    const VerificationUrl = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * {@inheritDoc}
     *
     * Verify the reCAPTCHA challenge with Google.
     * Remove the field from the form data if it was correct.
     */
    public function perform()
    {
        $recaptchaChallenge = kirby()->request()->get(self::FieldName, '');

        if (empty($recaptchaChallenge)) {
            $this->reject(t('uniform-recaptcha-empty'), self::FieldName);
        }

        $secretKey = option('expl0it3r.uniform-recaptcha.secretKey');

        if (empty($secretKey)) {
            throw new Exception('The reCAPTCHA secret key for Uniform is not configured');
        }

        $acceptableScore = option('expl0it3r.uniform-recaptcha.acceptableScore');

        // Verify the token with Google. Use a POST request so the secret stays
        // out of the URL (and any logs), and handle transport errors instead of
        // letting a raw file_get_contents() warning surface.
        try {
            $remote = Remote::post(self::VerificationUrl, [
                'data' => [
                    'secret'   => $secretKey,
                    'response' => $recaptchaChallenge,
                    'remoteip' => kirby()->visitor()->ip(),
                ],
            ]);

            $response = $remote->code() === 200 ? $remote->json() : null;
        } catch (Throwable $e) {
            // Network/transport failure: fail closed.
            $response = null;
        }

        if (
            empty($response) ||
            ($response['success'] ?? false) !== true ||
            ($response['score'] ?? 0) < $acceptableScore ||
            ($response['action'] ?? null) !== self::ActionName
        ) {
            $this->reject(t('uniform-recaptcha-invalid'), self::FieldName);
        }

        // Optional hostname check: only enforced when the option is configured.
        $expectedHostname = option('expl0it3r.uniform-recaptcha.hostname');

        if (!empty($expectedHostname) && ($response['hostname'] ?? null) !== $expectedHostname) {
            $this->reject(t('uniform-recaptcha-invalid'), self::FieldName);
        }

        $this->form->forget(self::FieldName);
    }
}
