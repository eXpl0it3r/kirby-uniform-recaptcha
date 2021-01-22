<?php

use Uniform\Guards\RecaptchaGuard;
use Uniform\Exceptions\Exception as UniformException;

if (!function_exists('recaptchaField')) {
    /**
     * Generate a reCAPTCHA form field
     *
     * @return string
     */
    function recaptchaField()
    {
        $siteKey = option('expl0it3r.uniform-recaptcha.siteKey');

        if (empty($siteKey)) {
            throw new UniformException('The reCAPTCHA sitekey for Uniform is not configured');
        }

        return '<div class="g-recaptcha" data-sitekey="'.$siteKey.'"></div>';
    }
}

if (!function_exists('recaptchaScript')) {
    /**
     * Generate script tag that includes the reCAPTCHA JavaScript file
     *
     * @return string
     */
    function recaptchaScript()
    {
        return '<script src="https://www.google.com/recaptcha/api.js"></script>';
    }
}
