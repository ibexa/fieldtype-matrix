<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\GraphQL\Schema;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\GraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\GraphQL\Schema\Domain\Content\Mapper\FieldDefinition\DecoratingFieldDefinitionMapper;

final class MatrixFieldDefinitionMapper extends DecoratingFieldDefinitionMapper implements FieldDefinitionMapper
{
    /**
     * @param iterable<\Ibexa\FieldTypeMatrix\FieldType\Mapper\FieldTypeToContentTypeStrategyInterface> $strategies
     */
    public function __construct(
        FieldDefinitionMapper $innerMapper,
        private readonly NameHelper $nameHelper,
        private readonly iterable $strategies
    ) {
        parent::__construct($innerMapper);
    }

    public function mapToFieldDefinitionType(FieldDefinition $fieldDefinition): string
    {
        return 'MatrixFieldDefinition';
    }

    protected function getFieldTypeIdentifier(): string
    {
        return 'ibexa_matrix';
    }

    /**
     * @throws \Ibexa\Core\Base\Exceptions\NotFoundException
     */
    public function mapToFieldValueType(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueType($fieldDefinition);
        }

        foreach ($this->strategies as $strategy) {
            $contentType = $strategy->findContentTypeOf($fieldDefinition);
            if ($contentType === null) {
                continue;
            }

            return sprintf(
                '[%s]',
                $this->nameHelper->matrixFieldDefinitionType($contentType, $fieldDefinition)
            );
        }

        throw new NotFoundException(
            'Could not find content type for field definition',
            $fieldDefinition->identifier
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
            '@=query("MatrixFieldValue", value, "%s")',
            $fieldDefinition->identifier
        );
    }
}
