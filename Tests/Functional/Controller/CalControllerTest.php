<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Tests\Functional\Controller;

use Mediadreams\MdFullcalendar\Controller\CalController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(CalController::class)]
final class CalControllerTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'lochmueller/calendarize',
        'mediadreams/md_fullcalendar',
    ];

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/md_fullcalendar/Tests/Functional/Controller/Fixtures/Sites/' => 'typo3conf/sites',
    ];

    protected array $configurationToUseInTestInstance = [
        'FE' => [
            'cacheHash' => [
                'enforceValidation' => false,
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/SiteStructure.csv');
        $this->setUpFrontendRootPage(1, [
            'constants' => [
                'EXT:md_fullcalendar/Configuration/TypoScript/constants.typoscript',
            ],
            'setup' => [
                'EXT:md_fullcalendar/Configuration/TypoScript/setup.typoscript',
                'EXT:md_fullcalendar/Tests/Functional/Controller/Fixtures/TypoScript/Rendering.typoscript',
            ],
        ]);
    }

    #[Test]
    public function showActionRendersExtensionTemplateAndLayout(): void
    {
        $request = (new InternalRequest())->withPageId(1);

        $response = $this->executeFrontendSubRequest($request);
        $html = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('class="tx-md-fullcalendar"', $html);
        self::assertStringContainsString('data-md-fullcalendar', $html);
        self::assertStringContainsString('calendar.js', $html);
    }

    #[Test]
    public function detailActionRendersExtensionTemplateAndLayout(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/CalendarizeEvent.csv');
        $request = (new InternalRequest())->withPageId(1)->withQueryParameters([
            'type' => 1573760945,
            'tx_mdfullcalendar_caldetail[index]' => 1,
        ]);

        $response = $this->executeFrontendSubRequest($request);
        $html = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('class="tx-md-fullcalendar"', $html);
        self::assertStringContainsString('Functional test event', $html);
        self::assertStringContainsString('class="modal-body"', $html);
    }

    #[Test]
    public function listActionReturnsBadRequestForInvalidEventQuery(): void
    {
        $request = (new InternalRequest())->withPageId(1)->withQueryParameter('type', 1573738558);

        $response = $this->executeFrontendSubRequest($request);
        $result = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame(['error' => 'Invalid event query.'], $result);
    }

    #[Test]
    public function listActionReturnsCalendarItemsAsJson(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/CalendarizeEvent.csv');
        $request = (new InternalRequest())->withPageId(1)->withQueryParameters([
            'type' => 1573738558,
            'start' => '2026-08-01T00:00:00+00:00',
            'end' => '2026-09-01T00:00:00+00:00',
            'storage' => '1',
        ]);

        $response = $this->executeFrontendSubRequest($request);
        $items = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringStartsWith('application/json', $response->getHeaderLine('Content-Type'));
        self::assertIsArray($items);
        self::assertCount(1, $items);
        $item = $items[0] ?? null;
        self::assertIsArray($item);
        self::assertSame(1, $item['id']);
        self::assertSame('Functional test event', $item['title']);
        self::assertSame('Rendered abstract', $item['abstract']);
    }

    #[Test]
    public function listActionRendersExtensionTemplateAndLayoutForRegularRequest(): void
    {
        $request = (new InternalRequest())->withPageId(1)->withQueryParameters([
            'tx_mdfullcalendar_cal[action]' => 'list',
            'start' => '2026-08-01T00:00:00+00:00',
            'end' => '2026-09-01T00:00:00+00:00',
        ]);

        $response = $this->executeFrontendSubRequest($request);
        $html = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringStartsWith('text/html', $response->getHeaderLine('Content-Type'));
        self::assertStringContainsString('class="tx-md-fullcalendar"', $html);
    }
}
