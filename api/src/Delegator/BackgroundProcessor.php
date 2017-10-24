<?php

namespace Daedalus\Delegator;

/**
 * This class will generate a new background process in the server
 */
class BackgroundProcessor
{
    /**
     * Process one route in the background
     * @todo if the container changes the volume location, this command may fail as the application console
     *       won't not exist anymore in /app/bin/console
     * @param string $token
     */
    public function processRoute(string $token) : void
    {
        exec(sprintf('nohup php /app/bin/console process-route %s > /dev/null 2>/dev/null &', $token));
    }

}
