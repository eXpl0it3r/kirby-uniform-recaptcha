<?php

namespace Uniform\Guards;

use ErrorException;
use Uniform\Exceptions\Exception;

/**
 * Uniform guard using Google reCAPTCHA
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
     * URL for the reCAPTCHA verification
     *
     * @var string
     */
    const VerificationUrl = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * {@inheritDoc}
     * 
     * Verify the reCAPTCHA challenge with Google
     * Remove the field from the form data if it was correct
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

        $requestUrl = self::VerificationUrl.'?secret='.$secretKey.'&response='.$recaptchaChallenge;

        $response = json_decode(file_get_contents($requestUrl), true);

        if (empty($response) || $response['success'] !== true) {
            $this->reject(t('uniform-recaptcha-invalid'), self::FieldName);
        }

        $this->form->forget(self::FieldName);
    }
}
