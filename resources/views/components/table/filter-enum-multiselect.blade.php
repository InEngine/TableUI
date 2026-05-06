{{--
    Enum filter with multiselect: dropdown list; selected labels wrap inside the trigger (no horizontal scrollbar).

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
        labelFor(value) {
            const v = String(value);
            return this.labels[value] ?? this.labels[v] ?? v;
        },
        get summary() {
            if (!Array.isArray(this.selected) || this.selected.length === 0) {
                return '';
            }
            return this.selected.map((x) => this.labelFor(x)).join(', ');
        },
        get hasSelection() {
            return Array.isArray(this.selected) && this.selected.length > 0;
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
    <div class="table-ui__filter-enum-multi-control w-full min-w-0 max-w-full overflow-hidden" x-ref="anchor">
        <button
            type="button"
            id="{{ $fieldId }}"
            class="table-ui__filter-enum-multi-trigger box-border min-h-[2.25rem] w-full min-w-0 max-w-full items-start gap-1 overflow-hidden rounded-md border border-gray-300 bg-white px-2 py-1.5 text-left text-sm text-gray-900 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400/35 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800/80 dark:focus:ring-gray-500/40"
            @click="open = !open"
            @if ($ariaLabelledby !== null) aria-labelledby="{{ $ariaLabelledby }}" @endif
            aria-haspopup="listbox"
            :aria-expanded="open"
            aria-controls="{{ $listboxId }}"
            :title="hasSelection ? summary : ''"
        >
            <span class="table-ui__filter-enum-multi-summary-scroll min-w-0 overflow-hidden">
                <span class="block min-w-0 break-words text-left leading-snug" x-text="hasSelection ? summary : allLabel"></span>
            </span>
            <span class="shrink-0 pt-0.5 text-gray-500 dark:text-gray-400" aria-hidden="true">
                {!! \InEngine\TableUI\Support\HeroiconOutlineSvg::inlineSvg('chevron-down', 'shrink-0') !!}
            </span>
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
