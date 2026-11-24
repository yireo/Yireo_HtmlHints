<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Integration;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Request;
use PHPUnit\Framework\TestCase;
use Yireo\HtmlHints\Config\Config;
use Yireo\HtmlHints\Observer\AddAdditionalDataObserver;
use Yireo\HtmlHints\Observer\AddHtmlHintsObserver;
use Yireo\HtmlHints\Plugin\AddContainerHintsPlugin;
use Yireo\HtmlHints\Util\HintGuard;

#[AppArea('frontend')]
#[AppIsolation(true)]
class RenderingTest extends TestCase
{
    #[ConfigFixture(Config::XML_PATH_ENABLED, '0', 'store', 'default')]
    public function testDisabledFlagLeavesBlockAndContainerUnchanged(): void
    {
        $layout = $this->getLayoutInMode(State::MODE_DEVELOPER);

        self::assertSame('<div>hello</div>', $this->renderBlock($layout));
        self::assertSame('<div><div>x</div></div>', $this->renderContainer($layout));
    }

    #[ConfigFixture(Config::XML_PATH_ENABLED, '1', 'store', 'default')]
    public function testDeveloperModeAddsBlockHintsWhenEnabled(): void
    {
        $layout = $this->getLayoutInMode(State::MODE_DEVELOPER);
        $html = $this->renderBlock($layout);

        self::assertStringContainsString('<!-- BLOCK CLASS:', $html);
        self::assertStringContainsString('<div>hello</div><!-- END BLOCK test.block -->', $html);
    }

    #[ConfigFixture(Config::XML_PATH_ENABLED, '1', 'store', 'default')]
    public function testDeveloperModeAddsContainerHintsWhenEnabled(): void
    {
        $layout = $this->getLayoutInMode(State::MODE_DEVELOPER);
        $html = $this->renderContainer($layout);

        self::assertStringStartsWith('<!-- CONTAINER NAME: test.container -->', $html);
        self::assertStringContainsString('<div>x</div>', $html);
        self::assertStringEndsWith('<!-- END CONTAINER NAME: test.container -->', $html);
    }

    #[ConfigFixture(Config::XML_PATH_ENABLED, '1', 'store', 'default')]
    public function testProductionModeLeavesBlockAndContainerUnchanged(): void
    {
        $layout = $this->getLayoutInMode(State::MODE_PRODUCTION);

        self::assertSame('<div>hello</div>', $this->renderBlock($layout));
        self::assertSame('<div><div>x</div></div>', $this->renderContainer($layout));
    }

    #[ConfigFixture(Config::XML_PATH_ENABLED, '1', 'store', 'default')]
    public function testFactoryLayoutIsNotAnnotated(): void
    {
        $sharedLayout = $this->getLayoutInMode(State::MODE_DEVELOPER);
        $factoryLayout = Bootstrap::getObjectManager()->get(LayoutFactory::class)->create();

        self::assertNotSame($sharedLayout, $factoryLayout);
        self::assertSame('<div>hello</div>', $this->renderBlock($factoryLayout));
        self::assertSame('<div><div>x</div></div>', $this->renderContainer($factoryLayout));
    }

    #[ConfigFixture(Config::XML_PATH_ENABLED, '1', 'store', 'default')]
    public function testAjaxRequestLeavesBlockAndContainerUnchanged(): void
    {
        $request = Bootstrap::getObjectManager()->get(RequestInterface::class);
        self::assertInstanceOf(Request::class, $request);
        $request->getHeaders()->addHeaderLine('X-Requested-With', 'XMLHttpRequest');
        $layout = $this->getLayoutInMode(State::MODE_DEVELOPER);

        self::assertTrue($request->isAjax());
        self::assertSame('<div>hello</div>', $this->renderBlock($layout));
        self::assertSame('<div><div>x</div></div>', $this->renderContainer($layout));
    }

    protected function tearDown(): void
    {
        $this->removeSharedHintServices();
        parent::tearDown();
    }

    private function getLayoutInMode(string $mode): LayoutInterface
    {
        $this->removeSharedHintServices();
        $objectManager = $this->getTestObjectManager();
        $state = $this->createMock(State::class);
        $state->method('getMode')->willReturn($mode);
        $config = $objectManager->create(Config::class, ['appState' => $state]);
        $objectManager->addSharedInstance($config, Config::class);

        return $objectManager->get(LayoutInterface::class);
    }

    private function removeSharedHintServices(): void
    {
        $objectManager = $this->getTestObjectManager();
        foreach ([
            AddAdditionalDataObserver::class,
            AddHtmlHintsObserver::class,
            AddContainerHintsPlugin::class,
            HintGuard::class,
            Config::class,
        ] as $class) {
            $objectManager->removeSharedInstance($class);
        }
    }

    private function getTestObjectManager(): ObjectManager
    {
        $objectManager = Bootstrap::getObjectManager();
        if (!$objectManager instanceof ObjectManager) {
            self::fail('Expected the Magento integration test object manager.');
        }

        return $objectManager;
    }

    private function renderBlock(LayoutInterface $layout): string
    {
        $block = $layout->createBlock(Text::class, 'test.block');
        self::assertInstanceOf(Text::class, $block);
        $block->setText('<div>hello</div>');

        return $block->toHtml();
    }

    private function renderContainer(LayoutInterface $layout): string
    {
        $layout->addContainer('test.container', 'Test', [Element::CONTAINER_OPT_HTML_TAG => 'div']);
        $block = $layout->addBlock(Text::class, 'test.container.child', 'test.container');
        self::assertInstanceOf(Text::class, $block);
        $block->setText('<div>x</div>');

        return $layout->renderElement('test.container');
    }
}
