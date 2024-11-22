<?php

namespace OCA\Collaboration;

class Util
{
    public static function isTrue($val, $return_null = false): bool
    {
        $boolval = (is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $val);
        return ($boolval === null && !$return_null ? false : $boolval);
    }
}
