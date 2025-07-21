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

function getRandomDefaultHashtags($qty = 20) {

    $hashtags = array(
        "abs",
        "bodybuilder",
        "bodybuilding",
        "diet",
        "exercise",
        "fit",
        "fitfam",
        "fitness",
        "fitnessmotivation",
        "functionalfitness",
        "functionaltraining",
        "gym",
        "gymmotivation",
        "health",
        "instafit",
        "model",
        "muscle",
        "shredded",
        "training",
        "workout",
    );

    $items = array_rand(array_flip($hashtags), $qty);
    return getHashtagString($items);
}

function getHashtagString($values) {
    return strtolower(
        implode(" ", preg_filter('/^/', '#', $values))
    );
}

?>