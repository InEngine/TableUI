{{--
    Enum filter with multiselect: dropdown list, clear (×), selected options use table primary + white text.

    @var string $wireModelPath  e.g. filterValues.status
    @var string $fieldId
    @var array<string, string> $enumOptions
    @var string|null $ariaLabelledby
--}}
@props([
    'wireModelPath',
    'fieldId',
    'enumOptions' => [],
    'ariaLabelledby' => null,
])
@php($listboxId = $fieldId.'-listbox')
@php($allLabel = __('All'))
<div
    class="table-ui__filter-enum-multi relative min-w-0 w-full max-w-full overflow-hidden"
    x-data="{
        open: false,
        panelStyle: {},
        labels: @js($enumOptions),
        allLabel: @js($allLabel),
        selected: $wire.entangle(@js($wireModelPath)).live,
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
        isOn(val) {
            const v = String(val);
            if (!Array.isArray(this.selected)) {
                return false;
            }
            return this.selected.some((x) => String(x) === v);
        },
        get summary() {
            if (!Array.isArray(this.selected) || this.selected.length === 0) {
                return '';
            }
            return this.selected
                .map((v) => this.labels[v] ?? this.labels[String(v)] ?? String(v))
                .join(', ');
        },
        get hasSelection() {
            return Array.isArray(this.selected) && this.selected.length > 0;
        },
        clear() {
            this.selected = [];
            this.open = false;
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
    <div class="table-ui__filter-enum-multi-control flex min-w-0 max-w-full items-stretch gap-0.5 overflow-hidden" x-ref="anchor">
        <button
            type="button"
            id="{{ $fieldId }}"
            class="table-ui__filter-enum-multi-trigger flex min-w-0 max-w-full flex-1 items-center justify-between gap-1 overflow-hidden rounded-md border border-gray-300 bg-white px-2 py-1.5 text-left text-sm text-gray-900 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400/35 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800/80 dark:focus:ring-gray-500/40"
            @click="open = !open"
            @if ($ariaLabelledby !== null) aria-labelledby="{{ $ariaLabelledby }}" @endif
            aria-haspopup="listbox"
            :aria-expanded="open"
            aria-controls="{{ $listboxId }}"
            :title="hasSelection ? summary : ''"
        >
            <span class="min-w-0 flex-1 truncate text-left" x-text="hasSelection ? summary : allLabel"></span>
            {!! \InEngine\TableUI\Support\HeroiconOutlineSvg::inlineSvg('chevron-down', 'shrink-0 text-gray-500 dark:text-gray-400') !!}
        </button>
        <button
            type="button"
            class="table-ui__filter-enum-multi-clear inline-flex shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white px-2 py-1.5 text-gray-500 shadow-sm transition-colors hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-400/35 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
            x-show="hasSelection"
            x-cloak
            @click.stop="clear()"
            aria-label="{{ __('Clear filter') }}"
        >
            {!! \InEngine\TableUI\Support\HeroiconOutlineSvg::inlineSvg('x-mark', 'h-4 w-4') !!}
        </button>
    </div>
    <ul
        x-cloak
        x-show="open"
        x-transition.opacity.duration.150ms
        id="{{ $listboxId }}"
        class="table-ui__filter-enum-multi-panel table-ui__filter-dropdown-panel fixed overflow-y-auto rounded-md border border-gray-200 bg-white py-1 text-left text-sm shadow-lg ring-1 ring-black/5 dark:border-gray-600 dark:bg-gray-900 dark:ring-white/10"
        :style="panelStyle"
        role="listbox"
        aria-multiselectable="true"
        aria-label="{{ __('Options') }}"
    >
        @foreach ($enumOptions as $value => $optionLabel)
            <li role="presentation">
                <button
                    type="button"
                    class="table-ui__filter-enum-multi-option w-full px-3 py-2 text-left text-sm transition-colors"
                    role="option"
                    :class="{ 'table-ui__filter-enum-multi-option--selected': isOn({{ json_encode((string) $value) }}) }"
                    :aria-selected="isOn({{ json_encode((string) $value) }})"
                    @click.stop="toggle({{ json_encode((string) $value) }})"
                >
                    <span class="block truncate">{{ $optionLabel }}</span>
                </button>
            </li>
        @endforeach
    </ul>
</div>
