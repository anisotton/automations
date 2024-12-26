<?php 

function str_contains_array($haystack, array $needles) {
    foreach ($needles as $needle) {
        if (str_contains($haystack, $needle)) {
            return true;
        }
    }
    return false;
}