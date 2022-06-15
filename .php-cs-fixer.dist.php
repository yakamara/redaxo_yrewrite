<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
;

return (new Redaxo\PhpCsFixerConfig\Config())
    ->setFinder($finder)
;
