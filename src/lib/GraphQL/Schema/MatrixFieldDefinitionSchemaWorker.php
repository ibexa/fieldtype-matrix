<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\GraphQL\Schema;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\GraphQL\Schema\Builder;
use Ibexa\GraphQL\Schema\Worker;

final readonly class MatrixFieldDefinitionSchemaWorker implements Worker
{
    public function __construct(private NameHelper $nameHelper)
    {
    }

    /**
     * @param array<string, mixed> $args
     */
    public function work(Builder $schema, array $args): void
    {
        $typeName = $this->typeName($args);
        $schema->addType(new Builder\Input\Type($typeName, 'object'));

        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition $fieldDefinition */
        $fieldDefinition = $args['FieldDefinition'];
        foreach ($fieldDefinition->getFieldSettings()['columns'] as $column) {
            $schema->addFieldToType(
                $typeName,
                new Builder\Input\Field(
                    $column['identifier'],
                    'String',
                    ['description' => $column['name']]
                )
            );
        }
    }

    /**
     * @param array<string, mixed> $args
     */
    public function canWork(Builder $schema, array $args): bool
    {
        return
            isset($args['ContentType'])
            && $args['ContentType'] instanceof ContentType
            && isset($args['FieldDefinition'])
            && $args['FieldDefinition'] instanceof FieldDefinition
            && $args['FieldDefinition']->fieldTypeIdentifier === 'ibexa_matrix'
            && !$schema->hasType($this->typeName($args));
    }

    /**
     * @param array<string, mixed> $args
     */
    private function typeName(array $args): string
    {
        return $this->nameHelper->matrixFieldDefinitionType(
            $args['ContentType'],
            $args['FieldDefinition']
        );
    }
}
