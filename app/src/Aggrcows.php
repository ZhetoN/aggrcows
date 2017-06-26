<?php

namespace Aggrcows;

class Aggrcows {

    public function getMinDistance($positions, $c) {

        sort($positions);
        // min distance
        $left  = 1;
        // max distance
        $right = end($positions) - $positions[0];

        while ($left <= $right) {
            // Set minimal distance in the middle between
            // $left (min distance) an $right (max distance)
            $minDistance = $left + intdiv($right - $left, 2);

            // Insert entry at first position
            $entries = 1;
            $lastPosition = $positions[0];

            // Count how many entries available in total space
            // with distance between greater or equal to the $minDistance
            $len = count($positions);
            for ($i = 1; $i < $len; $i++) {

                $distance = $positions[$i] - $lastPosition;

                if ($distance >= $minDistance) {
                    // Distance greater or equal to the $minDistance:
                    // insert one more entry
                    $entries++;
                    $lastPosition = $positions[$i];
                }
            }

            if ($entries >= $c) {
                // At least $c entries can be inserted
                // with $minDistance distance between.
                // try positions in range ($minDistance + 1, $right)
                $left = $minDistance + 1;
            } else {
                // Can't insert $c entries with $minDistance distance between
                // try positions in range ($left, $minDistance - 1)
                $right = $minDistance - 1;
            }
        }

        return $left - 1;
    }
}
