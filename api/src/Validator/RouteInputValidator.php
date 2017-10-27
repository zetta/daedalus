<?php

namespace Daedalus\Validator;

/**
 * Useful validations for Route Inputs
 */
class RouteInputValidator
{
    /**
     * Validate the user input
     * @param string $content
     * @return boolean
     */
    public function isValid(string $content) : bool
    {
        $json = json_decode($content, true);
        if (is_array($json) && count($json) > 1) {
            foreach ($json as $row) {
                if (!is_array($row) || count($row) != 2 || !is_numeric($row[0]) || !is_numeric($row[1]) ) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
