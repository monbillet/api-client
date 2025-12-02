<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setRules(['@PER-CS' => true]) // Do not set `'@autoPHPMigration' => true` because we still support PHP 7.1
    ->setFinder($finder);
