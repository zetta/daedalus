<?php

use Daedalus\Services\RouteService;
use Daedalus\Services\PathFinderService;
use Daedalus\Catalog\RouteCatalog;
use Daedalus\Exception\InvalidRouteException;
use PHPUnit\Framework\TestCase;

class RouteServiceTest extends TestCase
{

    protected $service;

    public function setup()
    {
        $this->service = new RouteService();
    }

    /**
     * @see testFindRouteByToken
     * @see testProcess
     */
    protected function mockFindRouteByToken($token, $route)
    {
        $catalog = $this->getMockBuilder(RouteCatalog::class)
             ->disableOriginalConstructor()
             ->getMock();

        $catalog->expects($this->once())
            ->method('findRouteByToken')
            ->with($token)
            ->willReturn($route);

        $this->service->setCatalog($catalog);
    }

    public function testFindRouteByToken()
    {
        $token = '1bc-a23';
        $route = ['id' => 1, 'uuid' => '1bc-a23'];

        $this->mockFindRouteByToken($token, $route);

        $this->assertEquals($route, $this->service->findRouteByToken($token));
    }

    /**
     * @dataProvider prepareRouteProvider
     */
    public function testPrepareRoute($route, $expected)
    {
        $this->assertEquals($expected, $this->service->prepareRoute($route));
    }

    public function prepareRouteProvider()
    {
        return [
            [
                ['path' => '[1]', 'error' => null, 'status' => 1],
                ['status' => 'new', 'path' => [1]],
            ],
            [
                ['path' => null, 'status' => 2],
                ['status' => 'in progress'],
            ],
            [
                ['path' => '[[1,2],[3,4],[5,6]]', 'status' => 3],
                ['status' => 'success', 'path' => [[1,2],[3,4],[5,6]]],
            ],
            [
                ['path' => null, 'error' => "some error", 'status' => 4],
                ['status' => 'failure', 'error' => 'some error'],
            ],
        ];
    }

    public function testStoreNew()
    {
        $json = '[[1,2],[3,4],[5,6]]';

        $catalog = $this->getMockBuilder(RouteCatalog::class)
             ->disableOriginalConstructor()
             ->getMock();

        // testing delegation
        $catalog->expects($this->once())
            ->method('save')
            ->with([
                'input' => $json,
                'status' => 1,
            ]);

        $this->service->setCatalog($catalog);
        $this->service->storeNew($json);
    }

    public function testProcess()
    {
        $token = '1bc-a23';
        $route = ['id' => 1, 'uuid' => '1bc-a23'];
        $path = [[1,2],[3,4],[5,6]];
        $statusUpdate = ['status' => 2];
        $statusSuccess = ['status' => 3];

        $this->mockFindRouteByToken($token, $route);
        $this->service->getCatalog()->expects($this->exactly(2))
            ->method('update')
            ->withConsecutive(
                [$route + $statusUpdate],
                [
                    $route +
                    $statusSuccess +
                    ['path' => json_encode($path)] +
                    ['total_distance' => 5, 'total_time' => 10]
                ]
            );

        $pathFinder = $this->getMockBuilder(PathFinderService::class)->getMock();

        $pathFinder->expects($this->once())
            ->method('findBestPath')
            ->willReturn([$path, 5, 10]);

        $this->service->setPathFinderService($pathFinder);

        $this->service->process($token);
    }

    public function testProcessFailure()
    {
        $token = '1bc-a23';
        $route = ['id' => 1, 'uuid' => '1bc-a23'];
        $path = [[1,2],[3,4],[5,6]];
        $statusUpdate = ['status' => 2];
        $statusError = ['status' => 4];

        $this->mockFindRouteByToken($token, $route);
        $this->service->getCatalog()->expects($this->exactly(2))
            ->method('update')
            ->withConsecutive(
                [$route + $statusUpdate],
                [$route + $statusError + ['error' => 'meh']]
            );

        $pathFinder = $this->getMockBuilder(PathFinderService::class)->getMock();

        $pathFinder->expects($this->once())
            ->method('findBestPath')
            ->will($this->throwException(new InvalidRouteException('meh')));

        $this->service->setPathFinderService($pathFinder);

        $this->service->process($token);
    }

}
