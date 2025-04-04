<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\FieldType;

use Ibexa\Contracts\Core\FieldType\Value as SPIValue;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\FieldType\FieldType;
use Ibexa\Core\FieldType\ValidationError;
use Ibexa\Core\FieldType\Value as FieldTypeValue;
use Ibexa\FieldTypeMatrix\FieldType\Value\Row;

class Type extends FieldType
{
    /**
     * {@inheritdoc}
     */
    protected $settingsSchema = [
        'minimum_rows' => [
            'type' => 'integer',
            'default' => 1,
        ],
        'columns' => [
            'type' => 'hash',
            'default' => [],
        ],
    ];

    private string $fieldTypeIdentifier;

    /**
     * @param string $fieldTypeIdentifier
     */
    public function __construct(string $fieldTypeIdentifier)
    {
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSortInfo(FieldTypeValue $value)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function validateFieldSettings($fieldSettings): array
    {
        $minimumRows = $fieldSettings['minimum_rows'];
        $columns = array_values($fieldSettings['columns'] ?? []);

        if (!is_numeric($minimumRows) || $minimumRows < 0) {
            $errors[] = new ValidationError(
                'Value must be numeric positive numeric.',
                null,
                [],
                'minimum_rows'
            );
        }

        foreach ($columns as $index => $column) {
            $trimmedIdentifier = trim($column['identifier'] ?? '');

            if (empty($trimmedIdentifier)) {
                $errors[] = new ValidationError(
                    'Column (index: %index%) must have identifier.',
                    null,
                    ['%index%' => $index]
                );
            } else {
                if (in_array($trimmedIdentifier, $identifiers ?? [])) {
                    $errors[] = new ValidationError(
                        'Identifier "%identifier%" must be unique.',
                        null,
                        ['%identifier%' => $trimmedIdentifier]
                    );
                } else {
                    $identifiers[] = $trimmedIdentifier;
                }
            }
        }

        return $errors ?? [];
    }

    /**
     * {@inheritdoc}
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_array($inputValue)) {
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldTypeIdentifier(): string
    {
        return $this->fieldTypeIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(SPIValue $value, FieldDefinition $fieldDefinition, string $languageCode): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getEmptyValue(): SPIValue
    {
        return new Value([]);
    }

    /**
     * {@inheritdoc}
     */
    public function fromHash($hash): SPIValue
    {
        $entries = $hash['entries'] ?? [];

        foreach ($entries as $row) {
            $rows[] = new Row($row);
        }

        return new Value($rows ?? []);
    }

    /**
     * {@inheritdoc}
     */
    protected function checkValueStructure(FieldTypeValue $value): void
    {
        // Value is self-contained and strong typed
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmptyValue(SPIValue $value): bool
    {
        /** @var \Ibexa\FieldTypeMatrix\FieldType\Value $value */
        return $value->getRows()->count() === 0;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Ibexa\FieldTypeMatrix\FieldType\Value $value
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return [];
        }

        $countNonEmptyRows = 0;

        foreach ($value->getRows() as $row) {
            if (!$row->isEmpty()) {
                ++$countNonEmptyRows;
            }
        }

        if ($countNonEmptyRows < $fieldDefinition->fieldSettings['minimum_rows']) {
            $validationErrors[] = new ValidationError(
                'Matrix must contain at least %minimum_rows% non-empty rows.',
                null,
                [
                    '%minimum_rows%' => $fieldDefinition->fieldSettings['minimum_rows'],
                ],
                $fieldDefinition->getName()
            );
        }

        return $validationErrors ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function toHash(SPIValue $value)
    {
        /** @var \Ibexa\FieldTypeMatrix\FieldType\Value $value */
        $rows = $value->getRows();

        $hash['entries'] = [];

        foreach ($rows as $row) {
            $hash['entries'][] = $row->getCells();
        }

        return $hash;
    }

    public function isSearchable(): bool
    {
        return true;
    }
}
