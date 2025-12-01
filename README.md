# Yireo HtmlHints

**A simple Magento 2 module that - once enabled - adds HTML hints to the frontend**
This module adds HTML hints (HTML comments `<!-- -->`) to the HTML output of a page, adding details on the rendered block or container. 

Note that this module creates a preference rewrite of the core interface `Magento\Framework\View\LayoutInterface` which might conflict with other modules.

### Installation
```bash
composer require yireo/magento2-html-hints
bin/magento module:enable Yireo_HtmlHints
```

### Usage
Before:
```html
<div class="page-title-wrapper">
    <h1 class="page-title" id="page-title-heading" aria-labelledby="page-title-heading toolbar-amount">
        <span class="base" data-ui-id="page-title-wrapper">Example</span>    
    </h1>
</div>
```

After:
```html
<!-- BLOCK CLASS: Magento\Theme\Block\Html\Title\Interceptor / BLOCK NAME: page.main.title / TEMPLATE NAME: Magento_Theme::html/title.phtml / TEMPLATE FILE: /var/www/html/vendor/magento/module-theme/view/frontend/templates/html/title.phtml -->
<div class="page-title-wrapper">
    <h1 class="page-title" id="page-title-heading" aria-labelledby="page-title-heading toolbar-amount">
        <span class="base" data-ui-id="page-title-wrapper">Example</span>
    </h1>
</div>
<!-- END BLOCK page.main.title -->
```
