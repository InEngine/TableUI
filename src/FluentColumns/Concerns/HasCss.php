<?php

namespace InEngine\TableUI\FluentColumns\Concerns;

use InEngine\TableUI\Support\TailwindMerge;

/**
 * Fluent CSS class helpers for legacy-style column descriptors.
 *
 * Compose with {@see CanBeHidden} before this trait on the concrete class so {@see isHidden()} exists for base class merging.
 */
trait HasCss
{
    protected string $cssClasses;

    /**
     * Default cell classes; override {@see $baseCssClasses} on the concrete column when needed.
     */
    protected string $baseCssClasses = 'whitespace-nowrap px-1 py-1 text-md text-left first:font-bold text-gray-600';

    /**
     * @return ($cssClasses is null ? string : static)
     */
    public function cssClasses(?string $cssClasses = null): static|string
    {
        if (is_string($cssClasses) && $cssClasses !== '') {
            $this->setCssClasses($cssClasses);

            return $this;
        }

        return $this->cssClasses;
    }

    public function getCssClasses(): string
    {
        return $this->cssClasses;
    }

    public function setCssClasses(?string $cssClasses): static
    {
        if ($cssClasses !== null && $cssClasses !== '') {
            $this->cssClasses = TailwindMerge::merge($this->getBaseCssClasses(), $cssClasses);
        } else {
            $this->cssClasses = $this->getBaseCssClasses();
        }

        return $this;
    }

    public function resetCss(): void
    {
        $this->cssClasses = $this->getBaseCssClasses();
    }

    public function getBaseCssClasses(): string
    {
        if ($this->isHidden()) {
            return TailwindMerge::merge($this->baseCssClasses, 'collapse');
        }

        return $this->baseCssClasses;
    }
}
