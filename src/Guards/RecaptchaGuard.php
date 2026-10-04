<?php

namespace Uniform\Guards;

use Kirby\Http\Remote;
use Throwable;
use Uniform\Exceptions\Exception;

/**
 * Uniform guard using Google reCAPTCHA v3 (score-based keys)
 */
class RecaptchaGuard extends Guard
{
    /**
     * reCAPTCHA HTML input field name
     */
    public const FieldName = 'g-recaptcha-response';

    /**
     * reCAPTCHA action name used
     */
    public const ActionName = 'UniformAction';

    /**
     * URL for the reCAPTCHA verification
     */
    public const VerificationUrl = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * {@inheritDoc}
     *
     * Verify the reCAPTCHA token with Google.
     * Remove the field from the form data if it was correct.
     */
    public function perform()
    {
        $token = kirby()->request()->get(self::FieldName, '');

        if (empty($token)) {
            $this->reject(t('uniform-recaptcha-empty'), self::FieldName);
        }

        $secretKey = option('expl0it3r.uniform-recaptcha.secretKey');

        if (empty($secretKey)) {
            throw new Exception('The reCAPTCHA secret key for Uniform is not configured');
        }

        $data = [
            'secret'   => $secretKey,
            'response' => $token,
            'remoteip' => kirby()->visitor()->ip(),
        ];

        // POST keeps the secret out of the URL and the server logs
        try {
            $remote = Remote::post(self::VerificationUrl, ['data' => $data]);
            $response = $remote->code() === 200 ? $remote->json() : null;
        } catch (Throwable) {
            // If Google can't be reached, the form must not go through unchecked
            $response = null;
        }

        if (
            is_array($response) === false ||
            ($response['success'] ?? false) !== true ||
            ($response['score'] ?? 0) < (float)option('expl0it3r.uniform-recaptcha.acceptableScore') ||
            ($response['action'] ?? null) !== self::ActionName
        ) {
            $this->reject(t('uniform-recaptcha-invalid'), self::FieldName);
        }

        $expectedHostname = option('expl0it3r.uniform-recaptcha.hostname');

        if (!empty($expectedHostname) && ($response['hostname'] ?? null) !== $expectedHostname) {
            $this->reject(t('uniform-recaptcha-invalid'), self::FieldName);
        }

        $this->form->forget(self::FieldName);
    }
}
