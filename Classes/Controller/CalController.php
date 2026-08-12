<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Controller;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use Mediadreams\MdFullcalendar\Domain\Repository\CategoryRepository;
use Mediadreams\MdFullcalendar\Service\EventCollectionBuilder;
use Mediadreams\MdFullcalendar\Service\EventQueryParser;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

final class CalController extends ActionController
{
    public function __construct(
        private readonly IndexRepository $indexRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly AssetCollector $assetCollector,
        private readonly EventQueryParser $eventQueryParser,
        private readonly EventCollectionBuilder $eventCollectionBuilder,
    ) {}

    public function showAction(): ResponseInterface
    {
        $this->assetCollector->addJavaScript(
            'md_fullcalendar_lib',
            'EXT:md_fullcalendar/Resources/Public/fullcalendar/dist/index.global.min.js',
        );

        $language = filter_var(
            $this->settings['language'] ?? null,
            FILTER_VALIDATE_REGEXP,
            ['options' => ['regexp' => '/\A[a-z]{2,3}(?:-[a-z]{2})?\z/D']],
        );
        if (is_string($language)) {
            $this->assetCollector->addJavaScript(
                'md_fullcalendar_locales',
                'EXT:md_fullcalendar/Resources/Public/fullcalendar/packages/core/locales/' . $language . '.global.js',
            );
        }

        $categoryUid = (int)($this->settings['category'] ?? 0);
        if ($categoryUid > 0) {
            $this->view->assign('categories', $this->categoryRepository->findByParent($categoryUid));
        }

        // pass storagePid to template in order to use it in ajax call listAction()
        $contentObjectRenderer = $this->request->getAttribute('currentContentObject');
        $contentObject = $contentObjectRenderer instanceof ContentObjectRenderer
            ? $contentObjectRenderer->data
            : [];

        $storagePid = $contentObject['pages'] ?? '';
        if (is_string($storagePid) && $storagePid !== '') {
            $this->view->assign('storagePid', $storagePid);
        }

        $this->view->assign('contentObject', $contentObject);

        return $this->htmlResponse();
    }

    public function listAction(): ResponseInterface
    {
        $requestBody = $this->request->getParsedBody();
        $parameters = array_replace(
            $this->request->getQueryParams(),
            is_array($requestBody) ? $requestBody : [],
        );

        try {
            $eventQuery = $this->eventQueryParser->parse($parameters);
        } catch (\InvalidArgumentException) {
            return $this->jsonResponse(
                json_encode(['error' => 'Invalid event query.'], JSON_THROW_ON_ERROR),
            )->withStatus(400);
        }

        $type = $parameters['type'] ?? null;
        if ($type === 1573738558 || $type === '1573738558') {
            $items = $this->eventCollectionBuilder->build(
                $eventQuery->start,
                $eventQuery->end,
                $this->uriBuilder,
                $this->getDetailPid(),
                $eventQuery->storagePageIds,
            );

            return $this->jsonResponse(json_encode($items, JSON_THROW_ON_ERROR));
        }

        $this->indexRepository->setOverridePageIds($eventQuery->storagePageIds);
        $this->view->assign('index', $this->indexRepository->findByTimeSlot($eventQuery->start, $eventQuery->end));

        return $this->htmlResponse();
    }

    public function detailAction(Index $index): ResponseInterface
    {
        $this->view->assign('index', $index);

        return $this->htmlResponse();
    }

    private function getDetailPid(): int
    {
        $pidSettings = $this->settings['pid'] ?? [];
        $detailPid = is_array($pidSettings) ? ($pidSettings['defaultDetailPid'] ?? null) : null;
        $validatedDetailPid = filter_var($detailPid, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return is_int($validatedDetailPid) ? $validatedDetailPid : 0;
    }
}
