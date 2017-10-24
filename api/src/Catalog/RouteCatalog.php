<?php

namespace Daedalus\Catalog;

use Daedalus\Traits\DatabaseConnectionAware;
use Daedalus\Exception\RouteNotFoundException;
use Ramsey\Uuid\Uuid;

/**
 * Class containing all the database specific operations
 */
class RouteCatalog
{
    use DatabaseConnectionAware;
    const TABLE_NAME = 'routes';

    /**
     * Find in the database the desired route by its uuid
     * @param string $token uuid of the route
     */
    public function findRouteByToken(string $token) : array
    {
        $sql = 'SELECT * FROM routes WHERE uuid = :uuid';
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue('uuid', $token);
        $statement->execute();
        $route = $statement->fetch();
        if (!$route) {
            throw new RouteNotFoundException('Unable to find route with token ' . $token);
        }
        return $route;
    }

    /**
     * Stores the route into the database
     * @param array
     */
    public function save(array $route) : string
    {
        $route['uuid'] = Uuid::uuid4()->toString();
        $this->getConnection()->insert(self::TABLE_NAME, $route);
        return $route['uuid'];
    }

    /**
     * Stores the route into the database
     */
    public function update(array $route) : array
    {
        $this->getConnection()->update(self::TABLE_NAME, $route, ['id' => $route['id']]);
        return $route;
    }

}
