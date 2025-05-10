<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\FieldTypeMatrix;

use Ibexa\Contracts\Core\Test\Repository\SetupFactory\Legacy as CoreLegacySetupFactory;
use Ibexa\Core\Base\ServiceContainer;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class LegacySetupFactory extends CoreLegacySetupFactory
{
    use CoreSetupFactoryTrait;

    public function getServiceContainer(): ServiceContainer
    {
        if (!isset(self::$serviceContainer)) {
            /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder */
            $containerBuilder = new ContainerBuilder();
            $this->externalBuildContainer($containerBuilder);
            self::$serviceContainer = new ServiceContainer(
                $containerBuilder,
                __DIR__,
                'var/cache',
                true,
                true
            );
        }

        return self::$serviceContainer;
    }

    protected function externalBuildContainer(ContainerBuilder $containerBuilder): void
    {
        $this->loadCoreSettings($containerBuilder);
        $this->loadMatrixFieldTypeSettings($containerBuilder);
    }

    private function loadMatrixFieldTypeSettings(ContainerBuilder $containerBuilder): void
    {
        $configPath = realpath(__DIR__ . '/../../src/bundle/Resources/config/');
        if (false === $configPath) {
            throw new RuntimeException('Unable to find FieldTypeMatrix package config');
        }

        $loader = new YamlFileLoader($containerBuilder, new FileLocator($configPath));
        $loader->load('services/fieldtype.yaml');

        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/_fixtures/config'));
        $loader->load('services.yaml');
    }
}
