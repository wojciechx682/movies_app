<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movie>
 */
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    // /**
    //  * @return Movie[]
    //  */
    // public function findAllWithGenres(): array
    // {
    //     return $this->createQueryBuilder('m')
    //         ->leftJoin('m.genres', 'g')
    //         ->addSelect('g')
    //         ->getQuery()
    //         ->getResult();
    // }

    public function findAllWithGenres(string $title = ''): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.genres', 'g')
            ->addSelect('g');

        if (trim($title) !== '') {
            $qb
                ->andWhere('m.title LIKE :title')
                ->setParameter('title', '%' . trim($title) . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return list<int>
     */
    public function findReleaseYears(): array
    {
        $results = $this->createQueryBuilder('m')
            ->select('DISTINCT m.releaseYear')
            ->orderBy('m.releaseYear', 'DESC')
            ->getQuery()
            ->getSingleColumnResult();

        $releaseYears = [];

        for ($i = 0; $i < count($results); $i++) {
            $releaseYears[] = (int) $results[$i];
        }

        return $releaseYears;
    }

    public function findFilteredMovies(
        ?string $title,
        ?string $genre,
        ?string $year,
        ?string $order
    ): array {
        
        /*
        Previous logic added each provided parameter independently:

        if ($title !== null && trim($title) !== '') {
            $qb
                ->andWhere('m.title LIKE :title')
                ->setParameter('title', '%' . trim($title) . '%');
        }

        if ($genre !== null) {
            $qb
                ->andWhere('g.name = :genre')
                ->setParameter('genre', $genre);
        }

        if ($year !== null) {
            $qb
                ->andWhere('m.releaseYear = :year')
                ->setParameter('year', (int) $year);
        }

        The sorting rules followed these conditions, so title search could also
        be affected by genre, year and order.
        */
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.genres', 'g')
            ->addSelect('g');

        if ($title !== null && trim($title) !== '') {
            
            $qb
                ->andWhere('m.title LIKE :title')
                ->setParameter('title', '%' . trim($title) . '%')
                ->orderBy('m.title', 'ASC')
                ->addOrderBy('m.id', 'ASC');

        } else {

            if ($genre !== null) {
                $qb
                    ->andWhere('g.name = :genre')
                    ->setParameter('genre', $genre);
            }

            if ($year !== null && trim($year) !== '') {
                $qb
                    ->andWhere('m.releaseYear = :year')
                    ->setParameter('year', (int) trim($year));
            }

            if ($order === 'title_asc') {
                $qb->orderBy('m.title', 'ASC')->addOrderBy('m.id', 'ASC');
            } elseif ($order === 'title_desc') {
                $qb->orderBy('m.title', 'DESC')->addOrderBy('m.id', 'ASC');
            } elseif ($order === 'year_desc') {
                $qb->orderBy('m.releaseYear', 'DESC')->addOrderBy('m.id', 'ASC');
            } elseif ($order === 'year_asc') {
                $qb->orderBy('m.releaseYear', 'ASC')->addOrderBy('m.id', 'ASC');
            } elseif ($order === 'created_at_desc') {
                $qb->orderBy('m.createdAt', 'DESC')->addOrderBy('m.id', 'ASC');
            } else {
                $qb->orderBy('m.id', 'ASC');
            }
        }

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Movie[] Returns an array of Movie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Movie
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
