<?php

namespace Daedalus\Traits;

/**
 * Useful if a class neds to be aware of a database catalog
 */
trait CatalogAware
{
    /**
     * @var Catalog
     */
    protected $catalog;

    /**
     * Gets the value of catalog.
     *
     * @return Catalog
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * Sets the value of catalog.
     *
     * @param Catalog $catalog the catalog
     *
     * @return self
     */
    public function setCatalog($catalog)
    {
        $this->catalog = $catalog;

        return $this;
    }
}
