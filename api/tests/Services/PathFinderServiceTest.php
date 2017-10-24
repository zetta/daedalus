<?php

use Daedalus\Services\PathFinderService;
use PHPUnit\Framework\TestCase;
use Ivory\GoogleMap\Service\Base\Distance;
use Ivory\GoogleMap\Service\Base\Duration;
use Ivory\GoogleMap\Service\DistanceMatrix\DistanceMatrixService;
use Ivory\GoogleMap\Service\DistanceMatrix\Response\DistanceMatrixResponse;
use Ivory\GoogleMap\Service\DistanceMatrix\Response\DistanceMatrixRow;
use Ivory\GoogleMap\Service\DistanceMatrix\Response\DistanceMatrixElement;

class PathFinderServiceTest extends TestCase
{

    protected $service;

    public function setup()
    {
        $this->service = new PathFinderService();
    }

    public function testFindBestPath()
    {
        $route = [
            'input' => '[["22.372081", "114.107877"],["22.284419", "114.159510"],["22.326442", "114.167811"]]'
        ];

        $matrixService = $this->getMockBuilder(DistanceMatrixService::class)->disableOriginalConstructor()->getMock();
        $response = $this->getMockBuilder(DistanceMatrixResponse::class)->disableOriginalConstructor()->getMock();
        $element = $this->getMockBuilder(DistanceMatrixElement::class)->disableOriginalConstructor()->getMock();
        $row = $this->getMockBuilder(DistanceMatrixRow::class)->disableOriginalConstructor()->getMock();
        $distance = $this->getMockBuilder(Distance::class)->disableOriginalConstructor()->getMock();
        $duration = $this->getMockBuilder(Duration::class)->disableOriginalConstructor()->getMock();

        $element->expects($this->any())
            ->method('getStatus')
            ->willReturn('OK');

        $element->expects($this->any())
            ->method('getDistance')
            ->willReturn($distance);

        $element->expects($this->any())
            ->method('getDuration')
            ->willReturn($duration);

        $duration->expects($this->any())
            ->method('getValue')
            ->will($this->onConsecutiveCalls(8, 7, 6, 5, 4, 3, 2, 1));

        $distance->expects($this->any())
            ->method('getValue')
            ->will($this->onConsecutiveCalls(8, 7, 6, 5, 4, 3, 2, 1)); // last element is closer

        $row->expects($this->any())
            ->method('getElements')
            ->willReturn([$element, $element]);

        $response->expects($this->once())
            ->method('getRows')
            ->willReturn([$row, $row, $row]);

        $matrixService->expects($this->once())
            ->method('process')
            ->willReturn($response);

        $this->service->setDistanceMatrixService($matrixService);

        $path = [["22.372081", "114.107877"],["22.326442", "114.167811"],["22.284419", "114.159510"]];

        $this->assertEquals([$path, 11, 11], $this->service->findBestPath($route));
    }

    /**
     * @dataProvider calculatePathProvider
     */
    public function testCalculatePath($input, $matrix, $expected)
    {
        $this->assertEquals($expected, $this->service->calculatePath($input, $matrix));
    }

    /**
     * DataProvider for testCalculatePath
     */
    public function calculatePathProvider()
    {
        return [
            [
                [[1,1],[2,2],[3,3], [4,4]],
                [
                    [[9,9], [5,5], [2,2]],
                    [[0,0], [6,6], [5,5]],
                    [[8,8], [0,0], [9,9]],
                    [[3,3], [6,6], [0,0]]
                ],
                [[[1,1],[4,4],[2,2], [3,3]], 2+3+6, 2+3+6]
            ],
            [
                [[1,1],[2,2],[3,3], [4,4]],
                [
                    [[9,9], [5,5], [7,7]],
                    [[0,0], [6,6], [5,5]],
                    [[6,6], [0,0], [9,9]],
                    [[3,3], [6,6], [0,0]]
                ],
                [[[1,1],[3,3],[2,2], [4,4]], 5+6+5, 5+6+5]
            ]
        ];
    }
}
