<?php

use Daedalus\Catalog\RouteCatalog;
use Daedalus\Exception\RouteNotFoundException;
use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;

class RouteCatalogTest extends TestCase
{

    protected $catalog;

    public function setup()
    {
        $this->catalog = new RouteCatalog();
    }

    /**
     * check if the db connection its being used in the update method
     */
    public function testUpdate()
    {
        $route = ['id' => 9, 'status' => 3];

        $connection = $this->getMockBuilder(Connection::class)
             ->disableOriginalConstructor()
             ->getMock();

        $connection->expects($this->once())
            ->method('update')
            ->with($this->equalTo(RouteCatalog::TABLE_NAME), $route, ['id' => 9]);

        $this->catalog->setConnection($connection);
        $this->catalog->update($route);
    }

    /**
     * check if the db connection its being used in the save method
     */
    public function testSave()
    {
        $route = ['id' => 9, 'status' => 3];

        $connection = $this->getMockBuilder(Connection::class)
             ->disableOriginalConstructor()
             ->getMock();

        $connection->expects($this->once())
            ->method('insert')
            ->with($this->equalTo(RouteCatalog::TABLE_NAME));
            // we just test the table name has been used as the uuid generator is static and requires
            // changes to make it testable.

        $this->catalog->setConnection($connection);
        $this->catalog->save($route);
    }

    public function testFindRouteByToken()
    {
        $token = '123';

        $connection = $this->getMockBuilder(Connection::class)
             ->disableOriginalConstructor()
             ->getMock();

        $statement = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())
            ->method('prepare')
            ->willReturn($statement);

        $statement->expects($this->once())
            ->method('bindValue')
            ->with('uuid', $token);

        $statement->expects($this->once())
            ->method('fetch')
            ->willReturn(['id' => 1]);

        $this->catalog->setConnection($connection);

        $this->catalog->findRouteByToken($token);
    }

    /**
     * @expectedException  Daedalus\Exception\RouteNotFoundException
     */
    public function testFindRouteByTokenThrowsException()
    {
        $connection = $this->getMockBuilder(Connection::class)
             ->disableOriginalConstructor()
             ->getMock();

        $statement = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())
            ->method('prepare')
            ->willReturn($statement);

        $statement->expects($this->once())
            ->method('fetch')
            ->willReturn(null);

        $this->catalog->setConnection($connection);

        $this->catalog->findRouteByToken('abc-123');
    }

}
