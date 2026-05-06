<?php

namespace InEngine\TableUI\Livewire;

use Illuminate\Contracts\View\View;
use InEngine\TableUI\TableServiceProvider;
use Livewire\Component;

/**
 * Livewire table cell: renders a single `<th>` or `<td>` with TableUI classes.
 *
 * Registered as the Livewire tag `tableui.column` (blade: `livewire:tableui.column`) when Livewire is installed.
 *
 * Intended for use inside `<tr>` elements alongside {@see Table}.
 *
 * @see TableServiceProvider::packageBooted()
 */
class Column extends Component
{
    /**
     * When true, renders a header cell (`<th scope="col">`); otherwise a body cell (`<td>`).
     */
    public bool $header = false;

    /**
     * Plain-text cell content.
     */
    public string $content = '';

    public function mount(bool $header = false, string $content = ''): void
    {
        $this->header = $header;
        $this->content = $content;
    }

    public function render(): View
    {
        /** @var view-string $view */
        $view = 'tableui::livewire.column';

        return view($view);
    }
}
