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
        $path = [];
        $steps = count($input);
        $distance = 0;
        $time = 0;

        $path[] = $input[0];

        $nextIndex = 0;
        $ignoredIndex = [];
        for ($i = 0; $i < $steps-1; $i++) {
            $shortest = $this->getShortest($matrix, $nextIndex, $ignoredIndex);
            $nextIndex = $shortest['index']+1;
            $ignoredIndex[] = $nextIndex-1;

            $distance += $shortest['values'][0];
            $time += $shortest['values'][1];

            $path[] = $input[$nextIndex];
        }

        return [$path, $distance, $time];
    }

    /**
     * Given the reduced matrix, calculate in the position where should the driver go next
     * @param array $matrix
     * @param int $position
     * @param int $ingoredIndex everytime a driver already have in his path one location we MUST ignore
     *       the same location to be compared, as there is no reason to visit the same location again
     * @return array in format
     *          [values => [meters, seconds], index => bestLocationToGoNext]
     */
    protected function getShortest(array $matrix, int $position, array $ignoreIndex) : array
    {
        $shortest = PHP_INT_MAX;
        $index = null;
        foreach($matrix[$position] as $idx => $values) {
            if ($values[0] < $shortest && $values[0] != 0 &&
                !in_array($idx, $ignoreIndex)) {
                $shortest = $values[0];
                $index = $idx;
            }
        }

        return [
            'values' => $matrix[$position][$index],
            'index' => $index,
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
}
