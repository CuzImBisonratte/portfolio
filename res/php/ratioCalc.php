<?php

function ratioCalc($clusterType, $position)
{

    $IMAGE_SIZES = array(
        'l' => 3 / 2,
        'le' => 3,
        'lee' => 9 / 2,
        'p' => 2 / 3,
        's' => 1 / 1
    );

    if (strpos($clusterType, 'e'))
        // Special case for extreme ratios
        return $IMAGE_SIZES[$clusterType];

    $imageType = substr($clusterType, $position, 1);
    return $IMAGE_SIZES[$imageType];
}
