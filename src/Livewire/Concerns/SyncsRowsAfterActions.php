<?php

namespace InEngine\TableUI\Livewire\Concerns;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use InEngine\TableUI\ActionTypes\ActionResponse;
use InEngine\TableUI\Livewire\TableView;
use RuntimeException;

/**
 * Keeps {@see TableView::$rows} and selection in sync after mutating row/bulk actions.
 */
trait SyncsRowsAfterActions
{
    /**
     * Remove rows from the in-memory dataset and drop their selection keys.
     *
     * @param  list<string>  $rowKeys
     */
    public function removeRowsByKeys(array $rowKeys): void
    {
        if ($rowKeys === []) {
            return;
        }

        $remove = array_fill_keys($rowKeys, true);

        $this->rows = array_values(array_filter(
            $this->rows,
            fn (array $row): bool => ! isset($remove[$this->rowKey($row)])
        ));

        $this->selectedRowKeys = array_values(array_diff($this->selectedRowKeys, $rowKeys));
        $this->bumpTableDataRevision();
        $this->clampPaginationPage();
    }

    /**
     * Merge attribute patches into rows matched by row key.
     *
     * @param  array<string, array<string, mixed>>  $patchesByRowKey
     */
    public function patchRowsByKeys(array $patchesByRowKey): void
    {
        if ($patchesByRowKey === []) {
            return;
        }

        $updated = [];

        foreach ($this->rows as $row) {
            $key = $this->rowKey($row);

            if (isset($patchesByRowKey[$key])) {
                $updated[] = array_merge($row, $patchesByRowKey[$key]);
            } else {
                $updated[] = $row;
            }
        }

        $this->rows = $updated;
        $this->bumpTableDataRevision();
    }

    protected function bumpTableDataRevision(): void
    {
        $this->tableDataRevision++;
    }

    /**
     * Apply an explicit {@see ActionResponse} or infer updates from the action name.
     *
     * @param  list<string>  $rowKeys
     */
    protected function syncTableAfterAction(string $actionName, array $rowKeys, mixed $closureResult = null): void
    {
        if ($closureResult instanceof ActionResponse) {
            $this->applyActionResponse($closureResult, $rowKeys);

            return;
        }

        if ($this->actionRemovesRows($actionName)) {
            $this->removeRowsByKeys($rowKeys);

            return;
        }

        $patches = $this->defaultPatchesForAction($actionName, $rowKeys);

        if ($patches !== []) {
            $this->patchRowsByKeys($patches);
        }
    }

    /**
     * True when a string-target row action should run in-process and refresh table rows (not navigate away).
     */
    protected function actionAppliesInPlace(string $actionName): bool
    {
        if (in_array($actionName, ['view', 'edit', 'row_link'], true)) {
            return false;
        }

        return $this->actionRemovesRows($actionName)
            || str_contains($actionName, 'unread')
            || str_contains($actionName, 'spam')
            || $actionName === 'update';
    }

    protected function actionRemovesRows(string $actionName): bool
    {
        return $actionName === 'delete' || str_ends_with($actionName, '_delete');
    }

    /**
     * @param  list<string>  $rowKeys
     * @return array<string, array<string, mixed>>
     */
    protected function defaultPatchesForAction(string $actionName, array $rowKeys): array
    {
        if ($rowKeys === []) {
            return [];
        }

        $patches = [];

        foreach ($rowKeys as $rowKey) {
            if (str_contains($actionName, 'unread') || $actionName === 'update') {
                $patches[$rowKey] = [
                    'has_been_read' => false,
                    'read_at' => null,
                ];
            } elseif (str_contains($actionName, 'spam')) {
                $patches[$rowKey] = ['is_spam' => true];
            }
        }

        return $patches;
    }

    /**
     * @param  list<string>  $rowKeys
     */
    protected function applyActionResponse(ActionResponse $response, array $rowKeys): void
    {
        if ($response->mode() === 'remove') {
            $keys = $response->removeRowKeys() !== [] ? $response->removeRowKeys() : $rowKeys;
            $this->removeRowsByKeys($keys);

            return;
        }

        if ($response->mode() === 'patch') {
            $this->patchRowsByKeys($response->patchesByRowKey());
        }
    }

    /**
     * Run a GET route in-process so host controllers can mutate data without a browser navigation.
     *
     * @throws RuntimeException When the sub-request returns an HTTP error status.
     */
    protected function invokeApplicationGetRoute(string $uri): void
    {
        /** @var Kernel $kernel */
        $kernel = app(Kernel::class);

        $request = Request::create($uri, 'GET');
        $request->headers->set('Accept', 'text/html');

        if (session()->isStarted()) {
            $request->setLaravelSession(session());
        }

        $request->setUserResolver(fn () => auth()->user());

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        if ($response->getStatusCode() >= 400) {
            throw new RuntimeException('Table action request failed with HTTP '.$response->getStatusCode().' for '.$uri);
        }
    }
}
