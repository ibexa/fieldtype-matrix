<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\Search\Elasticsearch;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\Content\Handler as ContentHandler;
use Ibexa\Contracts\Core\Search\Document;
use Ibexa\Contracts\Elasticsearch\Mapping\Event\ContentIndexCreateEvent;
use Ibexa\Contracts\Elasticsearch\Mapping\Event\LocationIndexCreateEvent;
use Ibexa\FieldTypeMatrix\Search\Common\IndexDataProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class IndexSubscriber implements EventSubscriberInterface
{
    private ContentHandler $contentHandler;

    private IndexDataProvider $indexDataProvider;

    public function __construct(ContentHandler $contentHandler, IndexDataProvider $indexDataProvider)
    {
        $this->contentHandler = $contentHandler;
        $this->indexDataProvider = $indexDataProvider;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentIndexCreateEvent::class => 'onContentIndexCreate',
            LocationIndexCreateEvent::class => 'onLocationIndexCreate',
        ];
    }

    public function onContentIndexCreate(ContentIndexCreateEvent $event): void
    {
        $this->appendSearchFields($event->getDocument(), $event->getContent());
    }

    public function onLocationIndexCreate(LocationIndexCreateEvent $event): void
    {
        $content = $this->contentHandler->load(
            $event->getLocation()->contentId
        );

        $this->appendSearchFields($event->getDocument(), $content);
    }

    private function appendSearchFields(Document $document, Content $content): void
    {
        $data = $this->indexDataProvider->getSearchData($content);
        foreach ($data as $field) {
            $document->fields[] = $field;
        }
    }
}
