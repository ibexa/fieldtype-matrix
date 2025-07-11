<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\GraphQL;

use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\GraphQL\Mutation\InputHandler\FieldTypeInputHandler;
use Ibexa\FieldTypeMatrix\FieldType\Value as MatrixValue;
use Ibexa\FieldTypeMatrix\FieldType\Value\Row;

final readonly class InputHandler implements FieldTypeInputHandler
{
    /**
     * @param array<string, mixed> $input
     */
    public function toFieldValue($input, mixed $inputFormat = null): Value
    {
        return new MatrixValue(
            array_map(
                static function (array $row): Row {
                    return new Row($row);
                },
                $input
            )
        );
    }
}
