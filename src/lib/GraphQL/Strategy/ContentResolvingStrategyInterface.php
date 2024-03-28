<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\GraphQL\Strategy;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;

interface ContentResolvingStrategyInterface
{
    public function resolveContent(object $item): Content;

    public function supports(object $item): bool;
}
