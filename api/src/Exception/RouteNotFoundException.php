<?php

namespace Daedalus\Exception;

use \RuntimeException;

/**
 * When an invalid token is sent to the api this exception will be thrown
 */
class RouteNotFoundException extends RuntimeException
{

}
