{{-- Client-side pager below the scroll region (see {@see TableView::$paginationPerPage}). --}}
@if ($this->paginationShouldShow && count($this->paginationVisiblePages) > 0)
    <nav class="table-ui__pagination" aria-label="{{ __('tableui::pagination.aria_navigation') }}" wire:key="table-ui-pagination-nav">
        <div class="table-ui__pagination-inner">
            <button
                type="button"
                class="table-ui__pagination-nav table-ui__pagination-nav--prev"
                wire:click="previousPaginationPage"
                title="{{ __('tableui::pagination.previous_page') }}"
                aria-label="{{ __('tableui::pagination.previous_page') }}"
                @if (!$this->paginationHasPrevious) disabled @endif
            >&lt;</button>

            @foreach ($this->paginationVisiblePages as $pageNum)
                <button
                    type="button"
                    wire:key="table-ui-pagination-page-{{ $pageNum }}"
                    wire:click="gotoPaginationPage({{ $pageNum }})"
                    @class([
                        'table-ui__pagination-page',
                        'table-ui__pagination-page--current' => $pageNum === $this->paginationPage,
                    ])
                    title="{{ __('tableui::pagination.go_to_page', ['page' => $pageNum]) }}"
                    aria-label="{{ __('tableui::pagination.go_to_page', ['page' => $pageNum]) }}"
                    @if ($pageNum === $this->paginationPage) aria-current="page" @endif
                >{{ $pageNum }}</button>
            @endforeach

            <button
                type="button"
                class="table-ui__pagination-nav table-ui__pagination-nav--next"
                wire:click="nextPaginationPage"
                title="{{ __('tableui::pagination.next_page') }}"
                aria-label="{{ __('tableui::pagination.next_page') }}"
                @if (!$this->paginationHasNext) disabled @endif
            >&gt;</button>
        </div>
    </nav>
@endif
