<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\FieldType\Value;

class Row
{
    /**
     * @param array<string, mixed> $cells
     */
    public function __construct(protected array $cells = [])
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getCells(): array
    {
        return $this->cells;
    }

    public function isEmpty(): bool
    {
        $trimmed = array_map('trim', $this->cells);
        $filtered = array_filter($trimmed, 'strlen');

        return count($filtered) === 0;
    }

    /**
     * @return array<string, mixed>|string
     */
    public function __get(string $name): array|string
    {
        return $this->cells[$name];
    }

    public function __isset(string $name): bool
    {
        return isset($this->cells[$name]);
    }
}
