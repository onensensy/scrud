<?php
if (!function_exists('h_extractModelName')) {
    function h_extractModelName($class)
    {
        return (str_replace([__namespace__, '\\', 'Controller'], '', __class__));
    }
}
