<?php

namespace Daedalus\Traits;

/**
 * Mostly used by catalog to be able to see the database connection
 */
trait DatabaseConnectionAware
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Gets the value of connection.
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Sets the value of connection.
     *
     * @param Connection $connection the connection
     *
     * @return self
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }
}
