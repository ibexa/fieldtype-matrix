<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\GraphQL\Strategy;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\GraphQL\Value\Item;

final class ItemContentResolvingStrategy implements ContentResolvingStrategyInterface
{
    public function resolveContent(object $item): Content
    {
        /** @var \Ibexa\GraphQL\Value\Item $item */
        return $item->getContent();
    }

    public function supports(object $item): bool
    {
        return $item instanceof Item;
    }
}
