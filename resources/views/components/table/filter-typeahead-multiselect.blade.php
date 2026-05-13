{{--
    Text / phone / email filter: type a substring needle (Enter to add) plus a multiselect list of distinct
    values from the current row payload (OR semantics via {@see TableView::$filterValues} list<string>).

    @var string $wireModelPath  Livewire path, e.g. filterValues.user_name
    @var string $fieldId
    @var string|null $ariaLabelledby
    @var iterable<mixed, mixed> $acOpts  Distinct suggestion strings from the column
    @var string $inputType  search|tel|email
    @var string|null $inputmode
--}}
@props([
    'wireModelPath',
    'fieldId',
    'ariaLabelledby' => null,
    'acOpts' => [],
    'inputType' => 'search',
    'inputmode' => null,
])
@php
    $distinctOpts = [];
    foreach ($acOpts as $_v) {
        $distinctOpts[(string) $_v] = (string) $_v;
    }
@endphp
@php($listboxId = $fieldId.'-listbox')
@php($inputId = $fieldId.'-needle')
<div
    class="table-ui__filter-typeahead-multi table-ui__filter-enum-multi relative min-w-0 w-full max-w-full overflow-hidden"
    x-data="{
        labels: @js($distinctOpts),
        selected: @entangle($wireModelPath).live,
        draft: '',
        open: false,
        panelStyle: {},
        positionPanel() {
            const el = this.$refs.anchor;
            if (!el || typeof el.getBoundingClientRect !== 'function') {
                return;
            }
            const r = el.getBoundingClientRect();
            const gap = 4;
            const spaceBelow = window.innerHeight - r.bottom - gap - 8;
            const maxH = Math.min(208, Math.max(96, spaceBelow));
            this.panelStyle = {
                position: 'fixed',
                left: Math.min(Math.max(8, r.left), window.innerWidth - r.width - 8) + 'px',
                top: r.bottom + gap + 'px',
                width: r.width + 'px',
                maxHeight: maxH + 'px',
                zIndex: 500,
            };
        },
        bindScrollReposition() {
            if (this._scrollBound) {
                return;
            }
            this._onScrollReposition = () => {
                if (this.open) {
                    this.positionPanel();
                }
            };
            const scrollEl = this.$refs.anchor?.closest('.table-ui__scroll');
            if (scrollEl) {
                scrollEl.addEventListener('scroll', this._onScrollReposition, { passive: true });
            }
            window.addEventListener('resize', this._onScrollReposition);
            document.addEventListener('scroll', this._onScrollReposition, true);
            this._scrollBound = true;
        },
        labelFor(value) {
            const v = String(value);
            return this.labels[v] ?? this.labels[value] ?? v;
        },
        get hasSelection() {
            return Array.isArray(this.selected) && this.selected.length > 0;
        },
        get optionKeys() {
            return Object.keys(this.labels ?? {});
        },
        get filteredOptionKeys() {
            const q = (this.draft ?? '').toString().toLowerCase().trim();
            const keys = this.optionKeys;
            if (!keys.length) {
                return [];
            }
            if (!q) {
                return keys.slice(0, 80);
            }
            return keys.filter((k) => {
                const label = String(this.labels[k] ?? k).toLowerCase();
                return label.includes(q) || String(k).toLowerCase().includes(q);
            });
        },
        commitDraft() {
            const t = (this.draft ?? '').toString().trim();
            if (t === '') {
                return;
            }
            let s = Array.isArray(this.selected) ? [...this.selected] : [];
            if (!s.some((x) => String(x) === t)) {
                s.push(t);
            }
            this.selected = s;
            this.draft = '';
        },
        toggle(rawVal) {
            const val = String(rawVal);
            let s = Array.isArray(this.selected) ? [...this.selected] : [];
            const i = s.findIndex((x) => String(x) === val);
            if (i === -1) {
                s.push(val);
            } else {
                s.splice(i, 1);
            }
            this.selected = s;
        },
        remove(rawVal) {
            const val = String(rawVal);
            if (!Array.isArray(this.selected)) {
                return;
            }
            this.selected = this.selected.filter((x) => String(x) !== val);
        },
        isOn(val) {
            const v = String(val);
            if (!Array.isArray(this.selected)) {
                return false;
            }
            return this.selected.some((x) => String(x) === v);
        },
        init() {
            this.$watch('open', (isOpen) => {
                if (isOpen) {
                    this.bindScrollReposition();
                    this.$nextTick(() => this.positionPanel());
                }
            });
        },
    }"
    @click.outside="open = false"
>
    <div class="table-ui__filter-typeahead-multi__stack w-full min-w-0 max-w-full space-y-1.5">
        <div
            class="table-ui__filter-typeahead-multi-chips flex max-h-28 min-h-0 flex-wrap gap-1 overflow-y-auto"
            x-show="hasSelection"
            x-cloak
        >
            <template x-for="tag in (selected ?? [])" :key="String(tag)">
                <button
                    type="button"
                    class="table-ui__filter-typeahead-multi-chip inline-flex max-w-full items-center gap-1 rounded-md bg-gray-100 px-2 py-0.5 text-left text-xs font-medium text-gray-800 ring-1 ring-inset ring-gray-200 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-600 dark:hover:bg-gray-700"
                    @click.stop="remove(tag)"
                >
                    <span class="min-w-0 truncate" x-text="labelFor(tag)"></span>
                    <span class="shrink-0 text-gray-500 dark:text-gray-400" aria-hidden="true">×</span>
                    <span class="sr-only">{{ __('Remove') }}</span>
                </button>
            </template>
        </div>

        <div class="table-ui__filter-enum-multi-control w-full min-w-0 max-w-full overflow-hidden" x-ref="anchor">
            <input
                id="{{ $inputId }}"
                type="{{ $inputType }}"
                @if ($inputmode !== null && $inputmode !== '') inputmode="{{ $inputmode }}" @endif
                class="table-ui__filter-input w-full min-w-0 rounded-md border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 shadow-sm focus:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400/35 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-gray-500 dark:focus:ring-gray-500/40"
                x-model="draft"
                @focus="open = true"
                @input="open = true"
                @keydown.enter.prevent="commitDraft(); open = filteredOptionKeys.length > 0"
                @keydown.escape.stop="open = false"
                placeholder="{{ __('Type to filter, Enter to add') }}"
                autocomplete="off"
                role="combobox"
                aria-autocomplete="list"
                aria-controls="{{ $listboxId }}"
                :aria-expanded="open && filteredOptionKeys.length > 0"
                @if ($ariaLabelledby !== null) aria-labelledby="{{ $ariaLabelledby }}" @endif
            />
        </div>
    </div>

    <ul
        x-cloak
        x-show="open && filteredOptionKeys.length > 0"
        x-transition.opacity.duration.150ms
        id="{{ $listboxId }}"
        class="table-ui__filter-enum-multi-panel table-ui__filter-dropdown-panel fixed overflow-y-auto rounded-md border border-gray-200 bg-white py-1 text-left text-sm shadow-lg ring-1 ring-black/5 dark:border-gray-600 dark:bg-gray-900 dark:ring-white/10"
        :style="panelStyle"
        role="listbox"
        aria-multiselectable="true"
        aria-label="{{ __('Distinct values') }}"
    >
        <template x-for="value in filteredOptionKeys" :key="String(value)">
            <li role="presentation">
                <button
                    type="button"
                    class="table-ui__filter-enum-multi-option w-full px-3 py-2 text-left text-sm transition-colors"
                    role="option"
                    :class="{ 'table-ui__filter-enum-multi-option--selected': isOn(value) }"
                    :aria-selected="isOn(value)"
                    @mousedown.prevent.stop="toggle(value)"
                >
                    <span class="block truncate" x-text="labelFor(value)"></span>
                </button>
            </li>
        </template>
    </ul>
</div>
