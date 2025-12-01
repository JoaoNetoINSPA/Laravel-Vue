<?php

// Create an array of 10 random numbers between 1 and 20;
// Filter numbers below 10 (using either a loop or array_filter);
// Output both the original array and the filtered array.

$originalArray = [];

for ($i = 0; $i <= 10; $i++) {
    $originalArray[] = rand(1, 20);
}

$filteredArray = array_filter($originalArray, function($k) {
    return $k < 10;
});


var_dump($originalArray);
var_dump($filteredArray);
