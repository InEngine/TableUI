<?php

namespace InEngine\TableUI;

use InEngine\TableUI\ActionTypes\DeleteAction;
use InEngine\TableUI\ActionTypes\EditAction;
use InEngine\TableUI\ActionTypes\RowLinkAction;
use InEngine\TableUI\ActionTypes\ViewAction;
use InEngine\TableUI\Contracts\BuildsDefaultTableAction;
use InEngine\TableUI\Support\RegisteredTableTypes;

/**
 * Builds the package default row/bulk actions for domain tables backed by Eloquent models.
 *
 * Targets follow {@code /{ShortClassName}/{id}/{action}} using {@see Action::resolveUrlFromStringTarget()} ({@code {id}} replacement).
 */
final class DefaultTableActions
{
    /**
     * View, edit, and delete row actions plus a {@see RowLinkAction} that mirrors the view URL for whole-row clicks.
     * Only {@see DeleteAction} is bulk-capable. Returns {@see Actions::empty()} when the table has no model rows.
     */
    public static function forTable(Table $table): Actions
    {
        $first = $table->first();

        if ($first === null) {
            return Actions::empty();
        }

        $segment = class_basename($first);

        $actions = [
            new RowLinkAction(target: self::path($segment, 'view')),
            new ViewAction(target: self::path($segment, 'view')),
            new EditAction(target: self::path($segment, 'edit')),
            new DeleteAction(target: self::path($segment, 'delete')),
        ];

        foreach (RegisteredTableTypes::mergedDefaultActionClasses() as $actionClass) {
            if (! is_subclass_of($actionClass, BuildsDefaultTableAction::class)) {
                continue;
            }

            $action = $actionClass::forTable($table);

            if ($action !== null) {
                $actions[] = $action;
            }
        }

        return new Actions($actions);
    }

    private static function path(string $resourceSegment, string $actionName): string
    {
        $segment = trim($resourceSegment, '/');

        return '/'.$segment.'/{id}/'.$actionName;
    }
}
