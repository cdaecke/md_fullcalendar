<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * Adds template layouts configured in Page TSconfig to the plugin's FlexForm selection.
 */
class TemplateLayouts
{
    /**
     * Appends valid and translated template layout items to the itemsProcFunc configuration.
     *
     * @param array<string, mixed> $config
     */
    public function getTemplateLayouts(array &$config): void
    {
        $pageUid = $config['effectivePid'] ?? 0;
        if (!is_int($pageUid)) {
            return;
        }

        $items = $config['items'] ?? [];
        if (!is_array($items)) {
            return;
        }

        $languageService = $GLOBALS['LANG'] ?? null;
        $templateLayouts = $this->getTemplateLayoutsFromTsConfig($pageUid);
        foreach ($templateLayouts as $index => $layout) {
            $item = $this->createItem($index, $layout, $languageService);
            if ($item !== null) {
                $items[] = $item;
            }
        }
        $config['items'] = $items;
    }

    /**
     * Creates an associative TCA select item for a configured template layout.
     *
     * @return array{label: string, value: string}|null
     */
    protected function createItem(int|string $index, mixed $layout, mixed $languageService): ?array
    {
        if (!is_string($layout)) {
            return null;
        }

        return [
            'label' => $languageService instanceof LanguageService ? $languageService->sL($layout) : $layout,
            'value' => (string)$index,
        ];
    }

    /**
     * Returns the template layouts configured for the page in Page TSconfig.
     *
     * @return array<int|string, mixed>
     */
    protected function getTemplateLayoutsFromTsConfig(int $pageUid): array
    {
        $pagesTsConfig = BackendUtility::getPagesTSconfig($pageUid);
        $extensionTsConfig = $pagesTsConfig['tx_mdfullcalendar_cal.'] ?? null;
        if (!is_array($extensionTsConfig)) {
            return [];
        }

        $templateLayouts = $extensionTsConfig['templateLayouts.'] ?? null;

        return is_array($templateLayouts) ? $templateLayouts : [];
    }
}
