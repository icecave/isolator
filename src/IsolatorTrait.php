<?php
namespace Icecave\Isolator;

/**
 * Trait for convenient isolator usage.
 */
trait IsolatorTrait
{
    /**
     * Get the {@see Isolator} instance used by this object.
     *
     * @return Isolator The isolator set via {@see IsolatorTrait::setIsolator()}, or the default isolator if none has been set.
     */
    public function isolator()
    {
        if ($this->isolator) {
            return $this->isolator;
        }

        return Isolator::get();
    }

    /**
     * Set the {@see Isolator} instance to be used by this object.
     *
     * @param Isolator|null $isolator The isolator instance to be used by this object, or null to use the global instance.
     */
    public function setIsolator(Isolator $isolator = null)
    {
        $this->isolator = $isolator;
    }

    private $isolator;
}
