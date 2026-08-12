<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Ssch\TYPO3Rector\CodeQuality\General\ExtEmConfRector;
use Ssch\TYPO3Rector\Configuration\Typo3Option;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Ssch\TYPO3Rector\Set\Typo3SetList;
use Ssch\TYPO3Rector\TYPO313\v4\MigratePluginContentElementAndPluginSubtypesRector;
use Ssch\TYPO3Rector\TYPO314\v0\DropFifthParameterForExtensionUtilityConfigurePluginRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../../Classes/',
        __DIR__ . '/../../Configuration/',
        __DIR__ . '/../../Tests/',
        __DIR__ . '/../../ext_emconf.php',
        __DIR__ . '/../../ext_localconf.php',
    ])
    ->withPhpSets()
    ->withComposerBased(phpunit: true)
    ->withSets([
        Typo3SetList::CODE_QUALITY,
        Typo3SetList::GENERAL,
        Typo3LevelSetList::UP_TO_TYPO3_14,
    ])
    ->withPHPStanConfigs([
        Typo3Option::PHPSTAN_FOR_RECTOR_PATH,
    ])
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
    ])
    // These v14-only migrations would break the required TYPO3 13.4 compatibility.
    ->withSkip([
        MigratePluginContentElementAndPluginSubtypesRector::class => [__DIR__ . '/../../ext_localconf.php'],
        DropFifthParameterForExtensionUtilityConfigurePluginRector::class => [__DIR__ . '/../../ext_localconf.php'],
        RenameClassRector::class => [__DIR__ . '/../../Classes/Upgrades/PluginListTypeToCTypeUpdate.php'],
    ])
    ->withImportNames(true, true, false)
    ->withConfiguredRule(ExtEmConfRector::class, [
        ExtEmConfRector::PHP_VERSION_CONSTRAINT => '8.2.0-8.5.99',
        ExtEmConfRector::TYPO3_VERSION_CONSTRAINT => '13.4.31-14.3.99',
        ExtEmConfRector::ADDITIONAL_VALUES_TO_BE_REMOVED => [],
    ]);
