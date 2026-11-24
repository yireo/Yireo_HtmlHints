<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Integration;

use Magento\Framework\App\Cache\StateInterface as CacheStateInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\State;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Layout as LayoutResult;
use Magento\PageCache\Model\Cache\Type as PageCacheType;
use Magento\PageCache\Model\Config as PageCacheConfig;
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
use Yireo\HtmlHints\Plugin\DisableFullPageCachePlugin;
use Yireo\HtmlHints\Plugin\EnableHintsDuringRenderPlugin;
use Yireo\HtmlHints\Plugin\SuspendHintsDuringEmailPlugin;
use Yireo\HtmlHints\Util\HintGuard;
use Yireo\HtmlHints\Util\RenderState;

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

    #[ConfigFixture(Config::XML_PATH_ENABLED, '1', 'store', 'default')]
    public function testSharedLayoutIsNotAnnotatedOutsideAResultRender(): void
    {
        $layout = $this->getLayoutInMode(State::MODE_DEVELOPER);

        self::assertSame('<div>hello</div>', $this->createTextBlock($layout)->toHtml());
        $this->addTestContainer($layout);
        self::assertSame('<div><div>x</div></div>', $layout->renderElement('test.container'));
    }

    #[ConfigFixture(Config::XML_PATH_ENABLED, '1', 'store', 'default')]
    public function testLayoutResultRenderAddsHints(): void
    {
        $layout = $this->getLayoutInMode(State::MODE_DEVELOPER);
        $result = $this->getTestObjectManager()->create(LayoutResult::class);
        self::assertSame($layout, $result->getLayout());
        $this->addTestContainer($layout);
        $layout->addOutputElement('test.container');
        $response = $this->getTestObjectManager()->create(HttpResponse::class);

        $result->renderResult($response);

        $body = (string)$response->getBody();
        self::assertStringContainsString('<!-- CONTAINER NAME: test.container -->', $body);
        self::assertStringContainsString('<!-- END BLOCK test.container.child -->', $body);
        self::assertFalse($this->getTestObjectManager()->get(RenderState::class)->isActive());
    }

    #[ConfigFixture(Config::XML_PATH_ENABLED, '1', 'store', 'default')]
    public function testFullPageCacheIsDisabledWhileHintsAreEnabled(): void
    {
        $this->getLayoutInMode(State::MODE_DEVELOPER);

        self::assertFalse($this->getPageCacheConfig()->isEnabled());
    }

    #[ConfigFixture(Config::XML_PATH_ENABLED, '0', 'store', 'default')]
    public function testFullPageCacheIsUnchangedWhileHintsAreDisabled(): void
    {
        $this->getLayoutInMode(State::MODE_DEVELOPER);

        self::assertTrue($this->getPageCacheConfig()->isEnabled());
    }

    #[ConfigFixture(Config::XML_PATH_ENABLED, '1', 'store', 'default')]
    public function testFullPageCacheIsUnchangedInProductionMode(): void
    {
        $this->getLayoutInMode(State::MODE_PRODUCTION);

        self::assertTrue($this->getPageCacheConfig()->isEnabled());
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
            DisableFullPageCachePlugin::class,
            EnableHintsDuringRenderPlugin::class,
            SuspendHintsDuringEmailPlugin::class,
            PageCacheConfig::class,
            HintGuard::class,
            RenderState::class,
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

    private function getPageCacheConfig(): PageCacheConfig
    {
        $objectManager = $this->getTestObjectManager();
        $objectManager->get(CacheStateInterface::class)->setEnabled(PageCacheType::TYPE_IDENTIFIER, true);

        return $objectManager->get(PageCacheConfig::class);
    }

    private function renderBlock(LayoutInterface $layout): string
    {
        return $this->whileRendering(fn (): string => $this->createTextBlock($layout)->toHtml());
    }

    private function renderContainer(LayoutInterface $layout): string
    {
        $this->addTestContainer($layout);

        return $this->whileRendering(fn (): string => $layout->renderElement('test.container'));
    }

    /**
     * @param callable(): string $callback
     */
    private function whileRendering(callable $callback): string
    {
        $state = $this->getTestObjectManager()->get(RenderState::class);
        $state->startRendering();
        try {
            return $callback();
        } finally {
            $state->stopRendering();
        }
    }

    private function createTextBlock(LayoutInterface $layout): Text
    {
        $block = $layout->createBlock(Text::class, 'test.block');
        self::assertInstanceOf(Text::class, $block);
        $block->setText('<div>hello</div>');

        return $block;
    }

    private function addTestContainer(LayoutInterface $layout): void
    {
        $layout->addContainer('test.container', 'Test', [Element::CONTAINER_OPT_HTML_TAG => 'div']);
        $block = $layout->addBlock(Text::class, 'test.container.child', 'test.container');
        self::assertInstanceOf(Text::class, $block);
        $block->setText('<div>x</div>');
    }
}
