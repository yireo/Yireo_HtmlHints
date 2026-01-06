<?php declare(strict_types=1);

namespace Yireo\HtmlHints\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddHtmlHintsObserver implements ObserverInterface
{

    public function execute(Observer $observer): void
    {
        $block = $observer->getEvent()->getBlock();
        $transport = $observer->getEvent()->getTransport();
        $html = $transport->getHtml();

        if (false === $this->allowChange($html)) {
            return;
        }

        $time = 'unknown';
        $startTime = (float)$block->getRenderStart();
        if ($startTime) {
            $time = round((microtime(true) - $startTime) * 1000, 2).'ms';
        }

        $comments = [];
        $comments[] = 'BLOCK CLASS: '.get_class($block);
        $comments[] = 'BLOCK NAME: '.$block->getNameInLayout();
        $comments[] = 'TEMPLATE NAME: '.$block->getTemplate();
        $comments[] = 'TEMPLATE FILE: '.$block->getTemplateFile();
        $comments[] = 'TIME: '.$time;

        $newHtml = '<!-- '.implode(' / ', $comments).' -->'
            . $html
            . '<!-- END BLOCK '.$block->getNameInLayout().' -->';
        $transport->setHtml($newHtml);
    }

    private function allowChange(mixed $result): bool
    {
        $state = ObjectManager::getInstance()->get(State::class);
        if ($state->getMode() !== State::MODE_DEVELOPER) {
            return false;
        }

        if (false === is_string($result)) {
            return false;
        }

        $html = trim((string)$result);
        if (empty($html)) {
            return false;
        }

        if (!preg_match('/^<([a-z]{2,})/', $html, $match)) {
            return false;
        }

        if (empty($match[1])) {
            return false;
        }

        return true;
    }
}
