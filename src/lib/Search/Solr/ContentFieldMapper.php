<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\Search\Solr;

use EzSystems\EzPlatformMatrixFieldtype\Search\Common\IndexDataProvider;
use Ibexa\Contracts\Core\Persistence\Content as SPIContent;
use Ibexa\Contracts\Solr\FieldMapper\ContentFieldMapper as BaseContentFieldMapper;

final class ContentFieldMapper extends BaseContentFieldMapper
{
    private IndexDataProvider $indexDataProvider;

    public function __construct(IndexDataProvider $indexDataProvider)
    {
        $this->indexDataProvider = $indexDataProvider;
    }

    public function accept(SPIContent $content): bool
    {
        return true;
    }

    public function mapFields(SPIContent $content): array
    {
        return $this->indexDataProvider->getSearchData($content);
    }
}
