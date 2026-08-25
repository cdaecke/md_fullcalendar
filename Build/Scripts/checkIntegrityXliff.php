<?php

declare(strict_types=1);

use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Util\XliffUtils;

require __DIR__ . '/../../.Build/vendor/autoload.php';

if (PHP_SAPI !== 'cli') {
    die('Script must be called from command line.' . chr(10));
}

$finder = (new Finder())
    ->files()
    ->in(__DIR__ . '/../../Resources/Private/Language')
    ->name('*.xlf');
$hasErrors = false;

foreach ($finder as $file) {
    $path = $file->getRealPath();
    $xml = simplexml_load_file($path);
    if ($xml === false) {
        fwrite(STDERR, $path . ': XML is not parsable.' . PHP_EOL);
        $hasErrors = true;
        continue;
    }

    $version = (string)$xml['version'];
    if (!in_array($version, ['1.2', '2.0'], true)) {
        fwrite(STDERR, $path . ': Unsupported XLIFF version ' . $version . '.' . PHP_EOL);
        $hasErrors = true;
        continue;
    }

    $schemaErrors = XliffUtils::validateSchema(XmlUtils::loadFile($path, null));
    if ($schemaErrors !== []) {
        foreach ($schemaErrors as $schemaError) {
            fwrite(STDERR, $path . ': ' . ($schemaError['message'] ?? 'XLIFF schema error.') . PHP_EOL);
        }
        $hasErrors = true;
        continue;
    }

    $namespace = $version === '1.2'
        ? 'urn:oasis:names:tc:xliff:document:1.2'
        : 'urn:oasis:names:tc:xliff:document:2.0';
    $xml->registerXPathNamespace('x', $namespace);
    $nodes = $version === '1.2'
        ? $xml->xpath('/x:xliff/x:file/x:body/x:trans-unit')
        : $xml->xpath('/x:xliff/x:file/x:unit');
    $identifiers = [];
    foreach ($nodes ?: [] as $node) {
        $identifier = (string)$node['id'];
        if ($identifier === '' || isset($identifiers[$identifier])) {
            fwrite(STDERR, $path . ': Missing or duplicate translation identifier: ' . $identifier . PHP_EOL);
            $hasErrors = true;
        }
        $identifiers[$identifier] = true;
    }
}

exit($hasErrors ? 1 : 0);
