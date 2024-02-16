<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\FieldType\Mapper;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

final class FieldTypeToContentTypeStrategy implements FieldTypeToContentTypeStrategyInterface
{
    private ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    public function findContentTypeOf(FieldDefinition $fieldDefinition): ?ContentType
    {
        foreach ($this->contentTypeService->loadContentTypeGroups() as $group) {
            foreach ($this->contentTypeService->loadContentTypes($group) as $type) {
                $foundFieldDefinition = $type->getFieldDefinition($fieldDefinition->identifier);
                if ($foundFieldDefinition === null) {
                    continue;
                }
                if ($foundFieldDefinition->id === $fieldDefinition->id) {
                    return $type;
                }
            }
        }

        return null;
    }
}
