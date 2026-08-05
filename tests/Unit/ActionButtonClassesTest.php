<?php

declare(strict_types=1);

use InEngine\TableUI\Livewire\TableView;

it('maps action names to composed btn classes', function (): void {
    $tableView = new TableView;

    expect($tableView->actionButtonClasses('delete'))->toBe('btn btn-delete')
        ->and($tableView->actionButtonClasses('bulk_contact_delete'))->toBe('btn btn-delete')
        ->and($tableView->actionButtonClasses('view'))->toBe('btn btn-view')
        ->and($tableView->actionButtonClasses('edit'))->toBe('btn btn-edit')
        ->and($tableView->actionButtonClasses('update'))->toBe('btn btn-edit')
        ->and($tableView->actionButtonClasses('bulk_contact_mark_unread'))->toBe('btn btn-edit')
        ->and($tableView->actionButtonClasses('bulk_contact_mark_read'))->toBe('btn btn-edit')
        ->and($tableView->actionButtonClasses('custom'))->toBe('btn btn-neutral');
});
