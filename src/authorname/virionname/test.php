<?php

$array = [];
$array2 = ["a" => 1, "b" => 2, "c" => 3];
$array3 = ["d" => 4, "e" => 5, "f" => 6];
array_push($array, $array2);
array_push($array, $array3);

var_dump($array);