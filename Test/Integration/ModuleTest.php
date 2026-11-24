<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Integration;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function testRequiredModulesAreRegisteredAndEnabled(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $registrar = $objectManager->get(ComponentRegistrarInterface::class);
        $moduleList = $objectManager->get(ModuleListInterface::class);

        foreach (['Yireo_HtmlHints', 'Magento_Store', 'Magento_Config', 'Magento_Backend', 'Magento_PageCache'] as $module) {
            self::assertNotEmpty($registrar->getPath(ComponentRegistrar::MODULE, $module), $module);
            self::assertTrue($moduleList->has($module), $module);
        }
    }
}
