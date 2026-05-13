<?php

namespace InEngine\TableUI\Concerns;

use InEngine\TableUI\Livewire\TableView;

/**
 * Marker trait for hosts migrating from {@code App\Components\Table\Traits\ToTable}.
 *
 * Composed on {@see TableView} so static analysis sees real usage inside this package; extend
 * with column-building hooks in your application when replacing legacy table builders.
 */
trait ToTable {}
