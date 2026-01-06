<?php declare(strict_types=1);

namespace Yireo\HtmlHints\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddAdditionalDataObserver implements ObserverInterface
{
    public function execute(Observer $observer): void
    {
        $block = $observer->getEvent()->getBlock();
        $block->setRenderStart(microtime(true));
    }
}
