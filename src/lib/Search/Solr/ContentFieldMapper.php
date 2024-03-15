<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\Search\Solr;

use Ibexa\Contracts\Core\Persistence\Content as SPIContent;
use Ibexa\Contracts\Core\Persistence\Content\Field;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Contracts\Core\Persistence\Content\Type\Handler as ContentTypeHandler;
use Ibexa\Contracts\Core\Search;
use Ibexa\Contracts\Solr\FieldMapper\ContentFieldMapper as BaseContentFieldMapper;
use Ibexa\FieldTypeMatrix\FieldType\Type;

final class ContentFieldMapper extends BaseContentFieldMapper
{
    private ContentTypeHandler $contentTypeHandler;

    public function __construct(ContentTypeHandler $contentTypeHandler)
    {
        $this->contentTypeHandler = $contentTypeHandler;
    }

    public function accept(SPIContent $content): bool
    {
        return true;
    }

    public function mapFields(SPIContent $content): array
    {
        $searchFields = [];

        $contentType = $this->contentTypeHandler->load(
            $content->versionInfo->contentInfo->contentTypeId
        );

        foreach ($content->fields as $field) {
            $definition = $this->findDefintion($contentType, $field);
            if ($definition === null || $definition->fieldType !== Type::FIELD_TYPE_IDENTIFIER) {
                continue;
            }

            $columns = array_column($definition->fieldTypeConstraints->fieldSettings['columns'], 'identifier');

            $data = $field->value->data;
            foreach ($data['entries'] as $column => $value) {
                $searchFields[] = new Search\Field(
                    $definition->identifier . '_col_' . $columns[$column] . '_value',
                    $value,
                    new Search\FieldType\MultipleStringField()
                );
            }
        }

        return $searchFields;
    }

    private function findDefintion(SPIContent\Type $contentType, Field $field): ?FieldDefinition
    {
        foreach ($contentType->fieldDefinitions as $definition) {
            if ($field->fieldDefinitionId === $definition->id) {
                return $definition;
            }
        }

        return null;
    }
}
