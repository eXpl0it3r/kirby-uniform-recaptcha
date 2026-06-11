<?php

use Uniform\Guards\RecaptchaGuard;
use Uniform\Exceptions\Exception as UniformException;

if (!function_exists('recaptchaField')) {
    /**
     * Generate the reCAPTCHA v3 hidden input field and the submit handler that
     * fetches a token via grecaptcha.execute() right before the form is sent.
     *
     * Place this inside the <form>, e.g. just before the submit button. The
     * handler binds to the input's own form, so no form id is needed and it is
     * safe to use multiple times on the same page. The original submitter (and
     * therefore its name/value, e.g. `form_id`) is preserved via requestSubmit().
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

        // HTML attribute context for the input name.
        $fieldAttr = esc(RecaptchaGuard::FieldName, 'attr');
        // JavaScript string-literal contexts (json_encode emits safe quoted literals).
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
        // requestSubmit() re-fires the submit event; skip on the second pass.
        if (submitting) { return; }
        e.preventDefault();

        var submitter = e.submitter || null;

        grecaptcha.ready(function () {
            grecaptcha.execute({$siteKeyJs}, { action: {$actionJs} }).then(function (token) {
                input.value = token;
                submitting = true;
                if (typeof form.requestSubmit === 'function') {
                    // Preserves the submitter's name/value (e.g. form_id).
                    form.requestSubmit(submitter);
                } else {
                    // Legacy fallback (e.g. Safari < 16): re-add the submitter
                    // as a hidden input so server-side routing still works.
                    if (submitter && submitter.name) {
                        var hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = submitter.name;
                        hidden.value = submitter.value;
                        form.appendChild(hidden);
                    }
                    form.submit();
                }
            }).catch(function () {
                // Allow the user to retry on failure.
                submitting = false;
            });
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
     * @deprecated Use recaptchaField() together with your own <button> instead.
     *             This function relies on the reCAPTCHA `data-callback` flow,
     *             which does not trigger when the form is submitted via the
     *             Enter key and requires knowing the form id. recaptchaField()
     *             binds to the form's submit event and works with multiple forms.
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
     * Generate the script tag that loads the reCAPTCHA v3 JavaScript API.
     *
     * reCAPTCHA v3 requires the site key to be passed via the `render`
     * query parameter so that grecaptcha.execute() can be called.
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
