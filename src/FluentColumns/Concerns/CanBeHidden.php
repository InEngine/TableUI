<?php

namespace InEngine\TableUI\FluentColumns\Concerns;

trait CanBeHidden
{
    protected bool $hidden = false;

    public function hide(): static
    {
        $this->hidden = true;

        return $this;
    }

    public function show(): void
    {
        $this->hidden = false;
    }

    public function toggleHidden(): static
    {
        $this->hidden = ! $this->hidden;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }
}
