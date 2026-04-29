<?php

namespace InEngine\TableUI;

use InvalidArgumentException;

/**
 * The Table UI Options class/object provides sane defaults for rendering tables with optional overrides.
 */
final class Options
{
    /**
     * @param  bool  $multipleSelect  Default: true
     * @param  bool  $linked  Default: true
     * @param  bool  $stripping  Default: true
     * @param  bool  $editable  Default: true
     * @param  string  $edit  Default: '/edit'
     * @param  bool  $deletable  Default: true
     * @param  string  $delete  Default: '/delete'
     * @param  bool  $detailable  Default: true
     * @param  string  $details  Default: '/'
     *
     * Pass only the arguments you need; omitted parameters keep the defaults above (use named arguments).
     *
     * @throws InvalidArgumentException When a flag is true but its route string is empty (after trim).
     */
    public function __construct(
        private bool $multipleSelect = true,
        private bool $linked = true,
        private bool $stripping = true,
        private bool $editable = true,
        private string $edit = '/edit',
        private bool $deletable = true,
        private string $delete = '/delete',
        private bool $detailable = true,
        private string $details = '/',
    ) {
        $this->assertRouteStringsMatchActiveFlags();
    }

    public function getMultipleSelect(): bool
    {
        return $this->multipleSelect;
    }

    public function setMultipleSelect(bool $multipleSelect): void
    {
        $this->multipleSelect = $multipleSelect;
    }

    public function getLinked(): bool
    {
        return $this->linked;
    }

    public function setLinked(bool $linked): void
    {
        $this->linked = $linked;
    }

    public function getStripping(): bool
    {
        return $this->stripping;
    }

    public function setStripping(bool $stripping): void
    {
        $this->stripping = $stripping;
    }

    public function getEditable(): bool
    {
        return $this->editable;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setEditable(bool $editable): void
    {
        $this->editable = $editable;

        $this->assertRouteStringsMatchActiveFlags();
    }

    /**
     * Returns the edit route only when {@see getEditable()} is true; otherwise an empty string.
     */
    public function getEdit(): string
    {
        if (! $this->editable) {
            return '';
        }

        return $this->edit;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setEdit(string $edit): void
    {
        $this->edit = $edit;

        $this->assertRouteStringsMatchActiveFlags();
    }

    public function getDeletable(): bool
    {
        return $this->deletable;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setDeletable(bool $deletable): void
    {
        $this->deletable = $deletable;

        $this->assertRouteStringsMatchActiveFlags();
    }

    /**
     * Returns the delete route only when {@see getDeletable()} is true; otherwise an empty string.
     */
    public function getDelete(): string
    {
        if (! $this->deletable) {
            return '';
        }

        return $this->delete;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setDelete(string $delete): void
    {
        $this->delete = $delete;

        $this->assertRouteStringsMatchActiveFlags();
    }

    public function getDetailable(): bool
    {
        return $this->detailable;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setDetailable(bool $detailable): void
    {
        $this->detailable = $detailable;

        $this->assertRouteStringsMatchActiveFlags();
    }

    /**
     * Returns the details route only when {@see getDetailable()} is true; otherwise an empty string.
     */
    public function getDetails(): string
    {
        if (! $this->detailable) {
            return '';
        }

        return $this->details;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setDetails(string $details): void
    {
        $this->details = $details;

        $this->assertRouteStringsMatchActiveFlags();
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertRouteStringsMatchActiveFlags(): void
    {
        if ($this->editable && $this->isEffectivelyEmptyString($this->edit)) {
            throw new InvalidArgumentException('When editable is true, edit must be a non-empty string.');
        }

        if ($this->deletable && $this->isEffectivelyEmptyString($this->delete)) {
            throw new InvalidArgumentException('When deletable is true, delete must be a non-empty string.');
        }

        if ($this->detailable && $this->isEffectivelyEmptyString($this->details)) {
            throw new InvalidArgumentException('When detailable is true, details must be a non-empty string.');
        }
    }

    private function isEffectivelyEmptyString(string $value): bool
    {
        return trim($value) === '';
    }
}
