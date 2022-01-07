<?php

if (!function_exists('numToAlphabet')) {
    function numToAlphabet($number)
    {
        $letters = range('A', 'Z');
        return $letters[$number];
    }
}
