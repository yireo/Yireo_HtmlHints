# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 25 November 2026
### Added
- Admin setting (Stores > Configuration > Advanced > Developer > HTML Hints) to switch hints on; it is off by default and still only works in developer mode
- Block hints now show the render time in microseconds
- PHP 8.5 support

### Changed
- Container hints are added through a plugin instead of a LayoutInterface preference, avoiding conflicts with other modules that override the layout

### Removed
- PHP 8.1 support

### Fixed
- Block names, class names and template paths are sanitized so they cannot break out of the HTML comment
- Hints are no longer injected into AJAX responses, non-page layouts or transactional emails
- Full page cache and block HTML cache are bypassed while hints are active, so hinted output is never cached

## [1.0.0] - 06 January 2026
### Initial release
- Add rendering time to block hints
- Also add observer to add hints to much more blocks
- Only enable in Developer Mode
- Initial concept
