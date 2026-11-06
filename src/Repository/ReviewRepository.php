<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @param list<Movie> $movies
     * @return array<int, array{average: float, count: int}>
     */
    public function getRatingSummariesForMovies(array $movies): array
    {
        if ($movies === []) {
            return [];
        }

        $movieIds = [];

        for ($i = 0; $i < count($movies); $i++) {
            $movieId = $movies[$i]->getId();

            if ($movieId !== null) {
                $movieIds[] = $movieId;
            }
        }

        if ($movieIds === []) {
            return [];
        }

        $results = $this->createQueryBuilder('r')
            ->select('m.id AS movieId, AVG(r.rating) AS average, COUNT(r.id) AS ratingCount')
            ->join('r.movie', 'm')
            ->andWhere('m.id IN (:movieIds)')
            ->setParameter('movieIds', $movieIds)
            ->groupBy('m.id')
            ->getQuery()
            ->getArrayResult();

        $ratingSummaries = [];

        /*
        for ($i = 0; $i < count($results); $i++) {
            $ratingSummaries[(int) $results[$i]['movieId']] = [
                'average' => (float) $results[$i]['average'],
                'count' => (int) $results[$i]['ratingCount'],
            ];
        }
        */

        for ($i = 0; $i < count($results); $i++) {
            $result = $results[$i];

            $movieId = (int) $result['movieId'];
            $average = (float) $result['average'];
            $ratingCount = (int) $result['ratingCount'];

            $ratingSummaries[$movieId] = [
                'average' => $average,
                'count' => $ratingCount,
            ];
        }

        return $ratingSummaries;
    }

//    /**
//     * @return Review[] Returns an array of Review objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Review
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
