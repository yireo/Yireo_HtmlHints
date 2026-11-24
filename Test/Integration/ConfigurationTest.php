<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Integration;

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\App\Config\Initial;
use Magento\Framework\Interception\DefinitionInterface;
use Magento\Framework\Interception\PluginListInterface;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Layout as LayoutResult;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\View\Layout as TestFrameworkLayout;
use PHPUnit\Framework\TestCase;
use Yireo\HtmlHints\Config\Config;
use Yireo\HtmlHints\Plugin\AddContainerHintsPlugin;
use Yireo\HtmlHints\Plugin\DisableFullPageCachePlugin;
use Yireo\HtmlHints\Plugin\EnableHintsDuringRenderPlugin;
use Yireo\HtmlHints\Plugin\SuspendHintsDuringEmailPlugin;

#[AppIsolation(true)]
class ConfigurationTest extends TestCase
{
    #[AppArea('frontend')]
    public function testFrontendLayoutUsesCoreClassAndRegistersHintPlugin(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $config = $objectManager->get(ConfigInterface::class);
        $preference = $config->getPreference(LayoutInterface::class);
        $layout = $objectManager->get(LayoutInterface::class);

        self::assertContains($preference, [Layout::class, TestFrameworkLayout::class]);
        self::assertContains(get_class($layout), [$preference, $preference . '\\Interceptor']);

        $reader = $objectManager->get(Dom::class);
        $globalDi = $reader->read('global');
        $frontendDi = $reader->read('frontend');
        $modulePreference = $frontendDi['preferences'][LayoutInterface::class]
            ?? $globalDi['preferences'][LayoutInterface::class]
            ?? Layout::class;
        self::assertSame(Layout::class, $modulePreference);

        $plugins = $objectManager->get(PluginListInterface::class);
        $code = 'Yireo_HtmlHints::addContainerHints';
        $chain = $plugins->getNext(Layout::class, 'renderNonCachedElement');
        self::assertContains($code, $chain[DefinitionInterface::LISTENER_AFTER] ?? []);
        self::assertInstanceOf(AddContainerHintsPlugin::class, $plugins->getPlugin(Layout::class, $code));

        $this->assertPluginRegistered(
            LayoutResult::class,
            'renderResult',
            'Yireo_HtmlHints::enableHintsDuringRender',
            DefinitionInterface::LISTENER_AROUND,
            EnableHintsDuringRenderPlugin::class
        );
        $this->assertPluginRegistered(
            TemplateInterface::class,
            'processTemplate',
            'Yireo_HtmlHints::suspendHintsDuringEmail',
            DefinitionInterface::LISTENER_AROUND,
            SuspendHintsDuringEmailPlugin::class
        );
        $this->assertPluginRegistered(
            PageCacheConfig::class,
            'isEnabled',
            'Yireo_HtmlHints::disableFullPageCache',
            DefinitionInterface::LISTENER_AFTER,
            DisableFullPageCachePlugin::class
        );
    }

    #[AppArea('adminhtml')]
    public function testAdminConfigurationDefinesFieldDisabledByDefault(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $field = $objectManager->get(Structure::class)->getElement(Config::XML_PATH_ENABLED);
        self::assertInstanceOf(Field::class, $field);
        self::assertSame('select', $field->getType());

        $defaults = $objectManager->get(Initial::class)->getData('default');
        self::assertSame('0', $defaults['dev']['html_hints']['enabled'] ?? null);
    }

    /**
     * @param class-string $type
     * @param class-string $pluginClass
     */
    private function assertPluginRegistered(
        string $type,
        string $method,
        string $code,
        int $listener,
        string $pluginClass
    ): void {
        $plugins = Bootstrap::getObjectManager()->get(PluginListInterface::class);
        $chain = $plugins->getNext($type, $method);
        $codes = $chain[$listener] ?? [];
        if (is_string($codes)) {
            $codes = [$codes];
        }

        self::assertContains($code, $codes, $type . '::' . $method);
        self::assertInstanceOf($pluginClass, $plugins->getPlugin($type, $code));
    }
}
