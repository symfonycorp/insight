<?php

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__.'/Cli', __DIR__.'/Sdk']);

$config = new PhpCsFixer\Config();
$config
    ->setRules(array(
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'class_attributes_separation' => ['elements' => ['const' => 'one']],
    ))
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;

return $config;
