# Yireo HtmlHints

<!-- badges.specs.start -->
<div class="github-specs-badges">

![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-html-hints)

</div>
<!-- badges.specs.end -->


**A simple Magento 2 module that - once enabled - adds HTML hints to the frontend**
This module adds HTML hints (HTML comments `<!-- -->`) to the HTML output of a page, adding details on the rendered block or container. 

### Installation
```bash
composer require yireo/magento2-html-hints
bin/magento module:enable Yireo_HtmlHints
```

### Configuration
HTML hints are disabled by default. Enable them via **Stores > Configuration > Advanced > Developer > HTML Hints > Enabled**. Note that the **Developer** section is only visible in the Admin Panel when Magento runs in developer mode. Alternatively, use the CLI:

```bash
bin/magento config:set dev/html_hints/enabled 1
```

Changing this setting in the Admin Panel requires the `Magento_Config::dev` admin permission (**Stores > Configuration > Developer** section).

Notes:
- Hints are only added when Magento is running in developer mode, even when the setting is enabled.
- Hints are only added while a storefront page result is rendered. They are skipped for AJAX requests, for layouts other than the main page layout and for emails.
- While hints are active, the full page cache and the block HTML cache are disabled, so hinted output is never stored in or served from these caches. After disabling hints, flush the caches with `bin/magento cache:flush`.

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

## Current status

<!-- badges.test.start -->
<div class="github-pipeline-badges">

![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_HtmlHints/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_HtmlHints/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_HtmlHints/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_HtmlHints/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_HtmlHints/compile.yml?label=compile)

</div>
<!-- badges.test.end -->
