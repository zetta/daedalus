<?php

namespace Daedalus\Traits;

use Silex\Application;

/**
 + If a class needs tobe able to see the whole container to injects its own dependencies
 */
trait ContainerAware
{
    /**
     * @var Catalog
     */
    protected $container;

    /**
     * @return Catalog
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Catalog $container
     *
     * @return self
     */
    public function setContainer(Application $container)
    {
        $this->container = $container;
        return $this;
    }
}
