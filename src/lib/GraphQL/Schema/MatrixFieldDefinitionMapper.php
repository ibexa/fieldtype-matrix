<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\GraphQL\Schema;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\GraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use Ibexa\GraphQL\Schema\Domain\Content\Mapper\FieldDefinition\DecoratingFieldDefinitionMapper;
use Ibexa\GraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionInputMapper;

class MatrixFieldDefinitionMapper extends DecoratingFieldDefinitionMapper implements FieldDefinitionMapper, FieldDefinitionInputMapper
{
    /** @var \Ibexa\FieldTypeMatrix\GraphQL\Schema\NameHelper */
    private $nameHelper;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    public function __construct(FieldDefinitionMapper $innerMapper, NameHelper $nameHelper, ContentTypeService $contentTypeService)
    {
        parent::__construct($innerMapper);
        $this->nameHelper = $nameHelper;
        $this->contentTypeService = $contentTypeService;
    }

    public function mapToFieldDefinitionType(FieldDefinition $fieldDefinition): ?string
    {
        return 'MatrixFieldDefinition';
    }

    protected function getFieldTypeIdentifier(): string
    {
        return 'ezmatrix';
    }

    public function mapToFieldValueType(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueType($fieldDefinition);
        }

        return sprintf(
            '[%s]',
            $this->nameHelper->matrixFieldDefinitionType($this->findContentTypeOf($fieldDefinition), $fieldDefinition)
        );
    }

    public function mapToFieldValueInputType(ContentType $contentType, FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition) && \is_callable('parent::mapToFieldValueInputType')) {
            return parent::mapToFieldValueInputType($contentType, $fieldDefinition);
        }

        return sprintf('[%s]', $this->nameHelper->matrixFieldDefinitionInputType($contentType, $fieldDefinition));
    }

    public function mapToFieldValueResolver(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueResolver($fieldDefinition);
        }

        // At this point 'value' is the Content item.
        // We can't "pass the definition" to the resolver. We need the columns names. Pass them to the resolver ??
        // An alternative is to
        return sprintf(
            '@=resolver("MatrixFieldValue", [value, "%s"])',
            $fieldDefinition->identifier
        );
    }

    private function findContentTypeOf(FieldDefinition $fieldDefinition): ContentType
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

        throw new \Exception('Could not find content type for field definition');
    }
}

class_alias(MatrixFieldDefinitionMapper::class, 'EzSystems\EzPlatformMatrixFieldtype\GraphQL\Schema\MatrixFieldDefinitionMapper');
