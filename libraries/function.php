<?php

function print_arr($array) {
    echo '<pre>';
    print_r($array);
    echo '<pre>';
}

if(!function_exists('mb_str_replace')) {
    function mb_str_replace($needle, $tex_replace, $haystack) {
        return implode($tex_replace, explode($needle, $haystack));
    }
}