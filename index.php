<?php

@include_once __DIR__ . '/vendor/autoload.php';

load([
    'Uniform\\Guards\\RecaptchaGuard' => 'src/Guards/RecaptchaGuard.php'
], __DIR__);

@include_once __DIR__ . '/src/helpers.php';

Kirby::plugin('expl0it3r/uniform-recaptcha', [
    'options' => [
        'siteKey' => '',
        'secretKey' => '',
        'acceptableScore' => 0.5,
        'hostname' => '',
        'sendRemoteIp' => false
    ],
    'translations' => [
        'de' => require __DIR__ . '/i18n/de.php',
        'en' => require __DIR__ . '/i18n/en.php',
        'nl' => require __DIR__ . '/i18n/nl.php'
    ]
]);
