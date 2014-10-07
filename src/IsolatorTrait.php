<?php
namespace Icecave\Isolator;

/**
 * Trait for convenient isolator usage.
 */
trait IsolatorTrait
{
    /**
     * Get the {@see IsolatorInterface} instance used by this object.
     *
     * @return IsolatorInterface The isolator set via {@see IsolatorTrait::setIsolator()}, or the default isolator if none has been set.
     */
    public function isolator()
    {
        if ($this->isolator) {
            return $this->isolator;
        }

        return Isolator::get();
    }

    /**
     * Set the {@see IsolatorInterface} instance to be used by this object.
     *
     * @param IsolatorInterface|null $isolator The isolator instance to be used by this object, or null to use the global instance.
     */
    public function setIsolator(IsolatorInterface $isolator = null)
    {
        $this->isolator = $isolator;
    }

    private $isolator;
}
