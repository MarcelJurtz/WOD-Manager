<?php

function findObjectById($array, $permalink)
{
    foreach ($array as $element) {
        if ($permalink == $element["permalink"]) {
            return $element;
        }
    }

    return false;
}

function debug_to_console($data)
{
    $output = json_encode($data);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

?>