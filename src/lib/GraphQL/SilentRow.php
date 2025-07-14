<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\GraphQL;

use Ibexa\FieldTypeMatrix\FieldType\Value\Row;

final class SilentRow extends Row
{
    /**
     * @return array<string, mixed>|string
     */
    public function __get(string $name): array|string
    {
        return $this->cells[$name] ?? '';
    }
}
