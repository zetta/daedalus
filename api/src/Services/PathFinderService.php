<?php

namespace Daedalus\Services;

use Daedalus\Traits\ContainerAware;
use Daedalus\Exception\InvalidRouteException;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Service\DistanceMatrix\DistanceMatrixService;
use Ivory\GoogleMap\Service\Base\Location\CoordinateLocation;
use Ivory\GoogleMap\Service\DistanceMatrix\Request\DistanceMatrixRequest;

/**
 * Service to find the best path given a route, it uses Google Api to get a matrix, then it will reduce
 * the map and generate a compressed matrix that can be inspected to calculate the shortest path and the final cost
 * in time & distance
 */
class PathFinderService
{
    use ContainerAware;

    const STATUS_OK = 'OK';

    /**
     * @var DistanceMatrixService
     */
    protected $distanceMatrixService;

    /**
     * @var MathService
     */
    protected $mathService;

    /**
     * @param array $route
     * @return array $path
     */
    public function findBestPath(array $route) : array
    {
        $input = json_decode($route['input']);
        $matrix = $this->getDistanceMatrix($input);

        return $this->calculatePath($input, $matrix);
    }

    /**
     * Will use google api to get the matrix and generate a basic matrix of data
     * @param array $input
     * @return array Reduced map with distance and duration
     */
    protected function getDistanceMatrix(array $input) : array
    {
        $origins = array_map(function($item){
            return new CoordinateLocation(new Coordinate($item[0], $item[1]));
        }, $input);

        array_shift($input); // we don't want our first point as a destination, because this is always the initial point

        $destinations = array_map(function($item) {
            return new CoordinateLocation(new Coordinate($item[0], $item[1]));
        }, $input);

        $response = $this->getDistanceMatrixService()->process(new DistanceMatrixRequest(
            $origins,
            $destinations
        ));

        return array_map(function($row) {
            return array_map(function($element) {
                if ($element->getStatus() != self::STATUS_OK) {
                    throw new InvalidRouteException('Can\'t process coordinate');
                }
                return [
                    $element->getDistance()->getValue(),
                    $element->getDuration()->getValue()
                ];
            }, $row->getElements());
        }, $response->getRows());
    }

    /**
     * Analize the reduced matrix and orders the input acording
     * @param array $input The provided input by the user
     * @param array $matrix Reduced matrix calculated by google
     * @return array in a form of
     *         [path, distance, time]
     *         where "path" is the best path found in this route, "distance" is the cost in meters
     *         and "time" is the cost in seconds
     */
    public function calculatePath(array $input, array $matrix) : array
    {
        $shortest = PHP_INT_MAX;
        $path = [];
        $finalCosts = [];

        foreach ($this->getMathService()->getPermutations(range(1, count($input)-1)) as $permutation) {
            $permutation = array_merge([0], $permutation);
            $costs = $this->calculateCosts($permutation, $matrix);
            if ($costs['distance'] < $shortest) {
                $shortest = $costs['distance'];
                $path = $permutation;
                $finalCosts = $costs;
            }
        }

        $path = array_map(function($item) use ($input) {
            return $input[$item];
        }, $path);

        return [$path, $finalCosts['distance'], $finalCosts['duration']];
    }

    /**
     * Given the delivery order, sum the costs of distance and duration of the path
     * @param array $order
     * @param array $matrix
     */
    public function calculateCosts(array $order, array $matrix) : array
    {
        $distance = 0;
        $duration = 0;
        $steps = count($order)-1;

        for ($i = 0; $i < $steps; $i++) {
            $from = $order[$i];
            $to = $order[$i+1];
            $costs = $matrix[$from][$to-1];
            $distance += $costs[0];
            $duration += $costs[1];
        }

        return [
            'distance' => $distance,
            'duration' => $duration,
        ];
    }

    /**
     * will use the dependency already injected, if no service is found will ask the container to
     * inject
     * @return DistanceMatrixService
     */
    public function getDistanceMatrixService() : DistanceMatrixService
    {
        if (null == $this->distanceMatrixService) {
            $this->distanceMatrixService = $this->container['google.distance.matrix.service'];
        }
        return $this->distanceMatrixService;
    }

    /**
     * @param DistanceMatrixService $distanceMatrixService
     *
     * @return self
     */
    public function setDistanceMatrixService(DistanceMatrixService $distanceMatrixService)
    {
        $this->distanceMatrixService = $distanceMatrixService;

        return $this;
    }

    /**
     * @return MathService
     */
    public function getMathService()
    {
        return $this->mathService;
    }

    /**
     * @param MathService $mathService
     *
     * @return self
     */
    public function setMathService(MathService $mathService)
    {
        $this->mathService = $mathService;

        return $this;
    }
}
