<?php

namespace Daedalus\Traits;

use Silex\Application;

/**
 + If a class needs tobe able to see the whole container to injects its own dependencies
 */
trait ContainerAware
{
    /**
     * @var Application
     */
    protected $container;

    /**
     * @return Application
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Application $container
     *
     * @return self
     */
    public function setContainer(Application $container)
    {
        $this->container = $container;
        return $this;
    }
}
