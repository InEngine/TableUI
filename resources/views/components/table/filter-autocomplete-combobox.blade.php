{{--
    Combobox autocomplete: suggestion panel below the input (requires Alpine, bundled with Livewire).

    @var string $wireModelPath  Livewire property path, e.g. filterValues.email or filterValues.amount.min
    @var list<string> $suggestions
--}}
@props([
    'wireModelPath',
    'fieldId',
    'suggestions',
    'inputType' => 'text',
    'inputmode' => null,
    'ariaLabel' => null,
    'ariaLabelledby' => null,
    'placeholder' => null,
    'extraInputClass' => '',
    'min' => null,
    'max' => null,
])
<div
    class="table-ui__filter-autocomplete relative min-w-0 w-full max-w-full"
    x-data="{
        suggestions: @js($suggestions),
        query: $wire.entangle(@js($wireModelPath)).live,
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
        get filtered() {
            const q = (this.query ?? '').toString().toLowerCase().trim();
            if (!this.suggestions?.length) {
                return [];
            }
            if (!q) {
                return this.suggestions.slice(0, 80);
            }
            return this.suggestions.filter((s) => String(s).toLowerCase().includes(q));
        },
        choose(v) {
            this.query = v;
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
    <input
        id="{{ $fieldId }}"
        x-ref="anchor"
        type="{{ $inputType }}"
        @if ($inputmode !== null && $inputmode !== '') inputmode="{{ $inputmode }}" @endif
        @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif
        @class([
            'w-full',
            $extraInputClass,
        ])
        @if ($ariaLabel !== null) aria-label="{{ $ariaLabel }}" @endif
        @if ($ariaLabelledby !== null) aria-labelledby="{{ $ariaLabelledby }}" @endif
        @if ($min !== null && $min !== '') min="{{ $min }}" @endif
        @if ($max !== null && $max !== '') max="{{ $max }}" @endif
        autocomplete="off"
        x-model="query"
        @focus="open = suggestions.length > 0"
        @input="open = suggestions.length > 0"
        @keydown.escape.stop="open = false"
        role="combobox"
        aria-autocomplete="list"
        aria-controls="{{ $fieldId }}-listbox"
        :aria-expanded="open && filtered.length > 0"
    />
    <ul
        x-cloak
        x-show="open && filtered.length > 0"
        x-transition.opacity.duration.150ms
        id="{{ $fieldId }}-listbox"
        class="table-ui__filter-autocomplete-panel table-ui__filter-dropdown-panel fixed overflow-y-auto rounded-md border border-gray-200 bg-white py-1 text-left text-sm shadow-lg ring-1 ring-black/5 dark:border-gray-600 dark:bg-gray-900 dark:ring-white/10"
        :style="panelStyle"
        role="listbox"
        aria-label="{{ __('Suggestions') }}"
    >
        <template x-for="(item, idx) in filtered" :key="idx">
            <li
                role="option"
                class="cursor-pointer px-3 py-1.5 text-gray-900 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-800"
                @mousedown.prevent="choose(item)"
                x-text="item"
            ></li>
        </template>
    </ul>
</div>
