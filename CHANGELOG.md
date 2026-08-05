# Changelog

All notable changes to `TableUI` will be documented in this file.

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
