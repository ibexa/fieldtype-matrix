<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\GraphQL\Schema;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

final readonly class NameHelper
{
    public function matrixFieldDefinitionType(ContentType $contentType, FieldDefinition $fieldDefinition): string
    {
        $caseConverter = new CamelCaseToSnakeCaseNameConverter(null, false);

        return sprintf(
            '%s%sRow',
            $caseConverter->denormalize($contentType->identifier),
            $caseConverter->denormalize($fieldDefinition->identifier)
        );
    }

    public function matrixFieldDefinitionInputType(ContentType $contentType, FieldDefinition $fieldDefinition): string
    {
        $caseConverter = new CamelCaseToSnakeCaseNameConverter(null, false);

        return sprintf(
            '%s%sRowInput',
            $caseConverter->denormalize($contentType->identifier),
            $caseConverter->denormalize($fieldDefinition->identifier)
        );
    }
}
