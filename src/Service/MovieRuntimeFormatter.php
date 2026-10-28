<?php

namespace App\Service;

use App\Entity\Movie;

class MovieRuntimeFormatter
{    
    public function formatRuntime(Movie $movie): ?string
    {
        $runtime = $movie->getRuntimeMinutes(); // 136 (int)

        if ($runtime === null) {
            return null;
        }

        if ($runtime < 0) {
            throw new \InvalidArgumentException('Runtime cannot be negative');
        }

        $hours = intdiv($runtime, 60); // dzielenie całkowite (bez części po przecinku)
        $minutes = $runtime % 60;      // pozostałe minuty --> (70 mod 60 == 10 minut)

        if ($hours === 0) {
            return $minutes . ' min';
        }

        if ($minutes === 0) {
            return $hours . ' h';
        }

        return $hours . 'h ' . $minutes . 'min';
    }
}