<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Tests\Functional\Configuration\Tca;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversNothing]
final class PluginRegistrationTest extends FunctionalTestCase
{
    private const PLUGIN_SIGNATURE = 'mdfullcalendar_cal';

    /** @var array<string, mixed> */
    private array $ttContentTca;

    protected array $testExtensionsToLoad = [
        'lochmueller/calendarize',
        'mediadreams/md_fullcalendar',
    ];

    protected bool $initializeDatabase = false;

    protected function setUp(): void
    {
        parent::setUp();

        $tca = $GLOBALS['TCA'] ?? null;
        self::assertIsArray($tca);
        $ttContentTca = $tca['tt_content'] ?? null;
        self::assertIsArray($ttContentTca);
        /** @var array<string, mixed> $ttContentTca */
        $this->ttContentTca = $ttContentTca;
    }

    #[Test]
    public function pluginIsRegisteredAsContentType(): void
    {
        $items = ArrayUtility::getValueByPath($this->ttContentTca, 'columns/CType/config/items');
        self::assertIsArray($items);

        $pluginItem = null;
        foreach ($items as $item) {
            if (is_array($item) && ($item['value'] ?? null) === self::PLUGIN_SIGNATURE) {
                $pluginItem = $item;
                break;
            }
        }

        self::assertIsArray($pluginItem);
        self::assertSame(
            'LLL:EXT:md_fullcalendar/Resources/Private/Language/locallang_db.xlf:tx_md_fullcalendar_cal.name',
            $pluginItem['label'],
        );
        self::assertSame('md_fullcalendar-plugin-cal', $pluginItem['icon']);
        self::assertSame('plugins', $pluginItem['group']);
        self::assertSame(
            'LLL:EXT:md_fullcalendar/Resources/Private/Language/locallang_db.xlf:tx_md_fullcalendar_cal.description',
            $pluginItem['description'],
        );
    }

    #[Test]
    public function pluginUsesConfiguredIcon(): void
    {
        self::assertSame(
            'md_fullcalendar-plugin-cal',
            ArrayUtility::getValueByPath(
                $this->ttContentTca,
                'ctrl/typeicon_classes/' . self::PLUGIN_SIGNATURE,
            ),
        );
    }

    #[Test]
    public function pluginUsesConfiguredFlexForm(): void
    {
        self::assertSame(
            'FILE:EXT:md_fullcalendar/Configuration/FlexForms/CalPlugin.xml',
            ArrayUtility::getValueByPath(
                $this->ttContentTca,
                'types/' . self::PLUGIN_SIGNATURE . '/columnsOverrides/pi_flexform/config/ds',
            ),
        );
    }

    #[Test]
    public function pluginTabShowsFlexFormPagesAndRecursionInThisOrder(): void
    {
        $showItem = ArrayUtility::getValueByPath(
            $this->ttContentTca,
            'types/' . self::PLUGIN_SIGNATURE . '/showitem',
        );
        self::assertIsString($showItem);

        $items = array_map('trim', explode(',', $showItem));
        $flexFormPosition = array_search('pi_flexform', $items, true);
        self::assertIsInt($flexFormPosition);
        self::assertStringContainsString('plugin', $items[$flexFormPosition - 1] ?? '');
        self::assertSame('pages', $items[$flexFormPosition + 1] ?? null);
        self::assertSame('recursive', $items[$flexFormPosition + 2] ?? null);
    }
}
