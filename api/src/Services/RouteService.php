<?php

namespace Daedalus\Services;

use Daedalus\Traits\CatalogAware;
use Daedalus\Exception\InvalidRouteException;

class RouteService
{
    use CatalogAware;

    const STATUS_NEW = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_SUCCESS = 3;
    const STATUS_FAILURE = 4;

    protected $status = [
        1 => 'new',
        2 => 'in progress',
        3 => 'success',
        4 => 'failure',
    ];

    /**
     * @var PathFinderService
     */
    protected $pathFinderService;

    /**
     * Finds into the catalog the route based on the token provied
     * @param string $token
     * @return array $route
     */
    public function findRouteByToken(string $token) : array
    {
        $route = $this->catalog->findRouteByToken($token);
        return $route;
    }

    /**
     * Prepares a route to be displayed in a json format
     * @param array $route to be manipulated
     */
    public function prepareRoute(array $route) : array
    {
        $route['path'] = json_decode($route['path'], true);
        $route = array_filter($route);
        unset($route['id']);
        unset($route['uuid']);
        unset($route['input']);
        $route['status'] = $this->status[$route['status']];
        return $route;
    }

    /**
     * Generates a route to be stored in the database
     */
    public function storeNew(string $jsonInput) : string
    {
        $route = [
            'input' => $jsonInput,
            'status' => self::STATUS_NEW,
        ];
        return $this->catalog->save($route);
    }

    /**
     * Process the route given a token
     * It will update the database according without locking the table.
     * So will generate two updates, first when its being processed and when the process finish
     * will include the new status.
     * @param string $token
     * @return void
     */
    public function process(string $token) : void
    {
        $route = $this->findRouteByToken($token);
        $route['status'] = self::STATUS_IN_PROGRESS;
        $this->catalog->update($route);

        try {
            $path = $this->getPathFinderService()->findBestPath($route);
            $route['status'] = self::STATUS_SUCCESS;
            $route['path'] = json_encode($path[0]);
            $route['total_distance'] = $path[1];
            $route['total_time'] = $path[2];
        } catch (InvalidRouteException $exception) {
            $route['status'] = self::STATUS_FAILURE;
            $route['error'] = $exception->getMessage();
        }

        $this->catalog->update($route);
    }

    /**
     * @return PathFinderService
     */
    public function getPathFinderService() : PathFinderService
    {
        return $this->pathFinderService;
    }

    /**
     * @param mixed $pathFinderService
     *
     * @return self
     */
    public function setPathFinderService(PathFinderService $pathFinderService)
    {
        $this->pathFinderService = $pathFinderService;
        return $this;
    }
}
