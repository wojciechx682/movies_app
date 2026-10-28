<?php

namespace App\Service;

use App\Entity\Movie;

class MovieRatingService
{
    /**
     * @return list<float>
     */
    public function calculateAverage(Movie $movie): array
    {
        $reviews = $movie->getReviews(); // Lazy Loading          

        $sum = 0; // suma recenzji (do obliczenia średniej wartości)
        $count = 0; // liczba recenzji
        $commentsCount = 0; // liczba komentarzy (do recenzji) - może być nullem, więc trzeba zabezpieczyć

        for ($i = 0; $i < count($reviews); $i++) {
            $rating = $reviews[$i]->getRating();
            $sum += $rating;
            $count++;

            $comment = $reviews[$i]->getContent();
            if ($comment !== null) {
                $commentsCount++;
            }
        }

        $ratingAverage = $count > 0 ? round($sum / $count, 1) : null; // 6.7
        $starDisplay = $count > 0 ? round(($sum / $count) * 2) / 2 : null; // 6.5
        $decimalPart = $starDisplay - ((int) floor($starDisplay)); // 0.5
        $remainingStars = 10 - ceil($starDisplay); // 3.0

        return [ 
            'reviews' => $reviews,
            'count' => $count,
            'commentsCount' => $commentsCount,            
            'ratingAverage' => $ratingAverage,
            'starDisplay' => $starDisplay,
            'decimalPart' => $decimalPart,
            'remainingStars' => $remainingStars
        ];
    }
}