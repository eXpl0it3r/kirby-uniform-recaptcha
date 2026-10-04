<?php

use Uniform\Guards\RecaptchaGuard;
use Uniform\Exceptions\Exception as UniformException;

if (!function_exists('recaptchaField')) {
    /**
     * Generate the hidden reCAPTCHA input and the script that requests a token right before the form is sent.
     *
     * Place it inside the <form>. The script binds to the form of its input, as such no form ID is needed
     * and multiple forms on the same page work. The submit button that was used keeps its name and value,
     * as the form is resubmitted with requestSubmit().
     *
     * @return string
     */
    function recaptchaField()
    {
        $siteKey = option('expl0it3r.uniform-recaptcha.siteKey');

        if (empty($siteKey)) {
            throw new UniformException('The reCAPTCHA sitekey for Uniform is not configured');
        }

        $jsFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;

        $fieldAttr = esc(RecaptchaGuard::FieldName, 'attr');
        $fieldJs = json_encode(RecaptchaGuard::FieldName, $jsFlags);
        $siteKeyJs = json_encode($siteKey, JSON_UNESCAPED_SLASHES | $jsFlags);
        $actionJs = json_encode(RecaptchaGuard::ActionName, $jsFlags);

        return <<<HTML
<input type="hidden" name="{$fieldAttr}" value="" data-uniform-recaptcha>
<script>
(function () {
    var script = document.currentScript;
    var input = script && script.previousElementSibling;
    if (!input || input.getAttribute('name') !== {$fieldJs}) {
        var f = script && script.closest && script.closest('form');
        input = f ? f.querySelector('input[data-uniform-recaptcha]') : null;
    }
    if (!input || !input.form) { return; }

    var form = input.form;
    var submitting = false;

    form.addEventListener('submit', function (e) {
        // requestSubmit() fires the event a second time, that one has to go through
        if (submitting) { return; }

        // Without the reCAPTCHA script there's no token and the server rejects the form with a message,
        // which is still better than a submit button that silently does nothing
        if (typeof grecaptcha === 'undefined') { return; }

        e.preventDefault();

        var submitter = e.submitter || null;

        var send = function () {
            submitting = true;
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit(submitter);
                return;
            }
            // Safari < 16 has no requestSubmit(), as such the button's name and value are added by hand
            if (submitter && submitter.name) {
                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = submitter.name;
                hidden.value = submitter.value;
                form.appendChild(hidden);
            }
            form.submit();
        };

        grecaptcha.ready(function () {
            grecaptcha.execute({$siteKeyJs}, { action: {$actionJs} }).then(function (token) {
                input.value = token;
                send();
            }, send);
        });
    });
})();
</script>
HTML;
    }
}

if (!function_exists('recaptchaButton')) {
    /**
     * Generate a reCAPTCHA form button and form submission callback.
     *
     * @deprecated Use recaptchaField() with your own <button> instead. The data-callback of this button
     *             doesn't trigger when the form is sent with the Enter key and it needs the ID of the form.
     *
     * @param string $text   The button text
     * @param string $class  Any additional CSS class entries
     * @param string $formId HTML ID of the to be submitted form
     *
     * @return string
     */
    function recaptchaButton($text, $class, $formId)
    {
        $siteKey = option('expl0it3r.uniform-recaptcha.siteKey');

        if (empty($siteKey)) {
            throw new UniformException('The reCAPTCHA sitekey for Uniform is not configured');
        }

        return '<script>function onRecaptchaFormSubmit(token) { document.getElementById("'.$formId.'").submit(); }</script>
        <button class="g-recaptcha '.$class.'" data-sitekey="'.$siteKey.'" data-callback="onRecaptchaFormSubmit" data-action="UniformAction">'.$text.'</button>';
    }
}

if (!function_exists('recaptchaScript')) {
    /**
     * Generate the script tag that loads the reCAPTCHA JavaScript API.
     * The site key has to be passed as render parameter, otherwise grecaptcha.execute() can't be called.
     *
     * @return string
     */
    function recaptchaScript()
    {
        $siteKey = option('expl0it3r.uniform-recaptcha.siteKey');

        if (empty($siteKey)) {
            throw new UniformException('The reCAPTCHA sitekey for Uniform is not configured');
        }

        return '<script src="https://www.google.com/recaptcha/api.js?render='.urlencode($siteKey).'"></script>';
    }
}
