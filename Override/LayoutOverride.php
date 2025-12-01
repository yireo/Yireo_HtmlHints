<?php declare(strict_types=1);

namespace Yireo\HtmlHints\Override;

use Magento\Framework\View\Layout;

class LayoutOverride extends Layout
{
    protected function _renderBlock($name)
    {
        $result = parent::_renderBlock(
            $name
        );

        if (false === $this->allowChange($result)) {
            return $result;
        }

        $block = $this->getBlock($name);

        $comments = [];
        $comments[] = 'BLOCK CLASS: '.get_class($block);
        $comments[] = 'BLOCK NAME: '.$block->getNameInLayout();
        $comments[] = 'TEMPLATE NAME: '.$block->getTemplate();
        $comments[] = 'TEMPLATE FILE: '.$block->getTemplateFile();

        $result = '<!-- '.implode(' / ', $comments).' -->'
            . $result
            . '<!-- END BLOCK '.$block->getNameInLayout().' -->';

        return $result;
    }

    protected function _renderContainer($name, $useCache = true)
    {
        $result = parent::_renderContainer(
            $name, $useCache
        );

        if (false === $this->allowChange($result)) {
            return $result;
        }

        $result = '<!-- CONTAINER NAME: '.$name.' -->' . $result . '<!-- END CONTAINER NAME: '.$name.' -->';
        return $result;
    }

    private function allowChange(mixed $result): bool
    {
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
