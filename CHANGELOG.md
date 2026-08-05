# Changelog

All notable changes to `TableUI` will be documented in this file.

## Version 1.0.4 - 2026-08-05

### What's Changed

* build(deps): bump spatie/laravel-package-tools from 1.93.0 to 1.93.1 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/22
* build(deps-dev): bump larastan/larastan from 3.9.6 to 3.10.0 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/23
* build(deps-dev): bump livewire/livewire from 4.3.0 to 4.3.1 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/24
* build(deps-dev): bump pestphp/pest from 4.7.0 to 4.7.2 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/25
* build(deps-dev): bump pestphp/pest from 4.7.2 to 4.7.3 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/26
* build(deps-dev): bump laravel/pint from 1.29.1 to 1.29.3 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/27
* build(deps-dev): bump pestphp/pest from 4.7.3 to 4.7.4 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/29
* build(deps-dev): bump livewire/livewire from 4.3.1 to 4.3.2 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/30
* build(deps-dev): bump phpstan/phpstan-phpunit from 2.0.16 to 2.0.17 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/31
* build(deps-dev): bump livewire/livewire from 4.3.2 to 4.3.3 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/32
* build(deps): bump actions/checkout from 6 to 7 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/28
* Actions: sync table rows in Livewire after row and bulk actions (#9) by @excellentingenuity in https://github.com/InEngine/TableUI/pull/34
* Bulk toolbar: Deselect All control and selection UX polish (#33) by @excellentingenuity in https://github.com/InEngine/TableUI/pull/37
* Add row emphasis criteria and bulk action button class mapping (#35) by @excellentingenuity in https://github.com/InEngine/TableUI/pull/38
* build(deps-dev): bump phpstan/phpstan-phpunit from 2.0.17 to 2.0.18 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/40
* build(deps-dev): bump pestphp/pest from 4.7.4 to 4.7.5 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/39
* build(deps-dev): bump nunomaduro/collision from 8.9.4 to 8.9.5 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/41
* build(deps): bump laravel/serializable-closure from 2.0.13 to 2.0.15 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/42
* build(deps-dev): bump phpstan/phpstan-deprecation-rules from 2.0.4 to 2.0.5 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/43
* Dev to merge for 1.0.4 by @excellentingenuity in https://github.com/InEngine/TableUI/pull/45

**Full Changelog**: https://github.com/InEngine/TableUI/compare/v1.0.3...v1.0.4

## Unreleased

### What's Changed

* Actions: sync table rows in Livewire after row and bulk actions (#9) by @excellentingenuity in https://github.com/InEngine/TableUI/pull/34
  * `ActionResponse` (`removeRows` / `patchRows` / `none`, plus `*ForRows` helpers)
  * `action_id_key` config + `Options` / `TableRowActionId` for selection keys and `{id}` URL tokens
  * In-process mutating actions refresh the Livewire row set without a full page reload
  
* Bulk toolbar: Deselect All control and selection UX polish (#33) by @excellentingenuity in https://github.com/InEngine/TableUI/pull/37
* Add row emphasis criteria and bulk action button class mapping (#35) by @excellentingenuity in https://github.com/InEngine/TableUI/pull/38
  * `Options::rowEmphasis` / `RowEmphasis` (`bold`, `highlight`)
  * Bulk delete / mark read / mark unread actions use the same `btn-*` classes as row counterparts
  

## Version 1.0.3 - 2026-05-22

### What's Changed

* fix(filters): blank date/datetime defaults and skip inactive ranges by @excellentingenuity in https://github.com/InEngine/TableUI/pull/21

**Full Changelog**: https://github.com/InEngine/TableUI/compare/v1.0.2...v1.0.3

## Version 1.0.2 - 2026-05-21

### What's Changed

* Update composer.json by @excellentingenuity in https://github.com/InEngine/TableUI/pull/20

**Full Changelog**: https://github.com/InEngine/TableUI/compare/v1.0.1...v1.0.2

## Version 1.0.1 - 2026-05-21

### What's Changed

* build(deps): bump dependabot/fetch-metadata from 3.0.0 to 3.1.0 by @dependabot[bot] in https://github.com/InEngine/TableUI/pull/6
* fix(phpstan): Table/fromCollection accepts concrete Eloquent collections (#3) by @excellentingenuity in https://github.com/InEngine/TableUI/pull/11
* feat(fluent-columns): PHPStan-friendly column builders and tw_merge (#4) by @excellentingenuity in https://github.com/InEngine/TableUI/pull/12
* fix: redundant type checks + ToTable marker trait (#5) by @excellentingenuity in https://github.com/InEngine/TableUI/pull/13
* fix(livewire): filter before sort for client-side pagination (#7) by @excellentingenuity in https://github.com/InEngine/TableUI/pull/16
* fix(livewire): client sort defaults, id inference, stable ties (#15) by @excellentingenuity in https://github.com/InEngine/TableUI/pull/17
* feat(filters): typeahead multiselect, partial search, enum dropdowns (#8) by @excellentingenuity in https://github.com/InEngine/TableUI/pull/18
* Dev by @excellentingenuity in https://github.com/InEngine/TableUI/pull/19

### New Contributors

* @excellentingenuity made their first contribution in https://github.com/InEngine/TableUI/pull/11

**Full Changelog**: https://github.com/InEngine/TableUI/compare/v1.0.0...v1.0.1

## Version 1.0.0 - 2026-05-06

The first release of TableUI, used for Laravel and InEngine based applications.
