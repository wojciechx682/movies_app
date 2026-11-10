<?php

namespace App\Controller;

use App\Repository\GenreRepository;
use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    // private const MOVIES_PER_PAGE = 8;
    private const MOVIES_PER_PAGE = 12;

//    #[Route('/home', name: 'app_home')]
//    public function index(): Response
//    {
//        return $this->render('home/index.html.twig', [
//            'controller_name' => 'HomeController',
//        ]);
//    }

    #[Route('/home', name: 'app_home')]
    public function index(
        MovieRepository $movieRepository,
        GenreRepository $genreRepository,        
        #[MapQueryParameter] ?string $title = null,
        #[MapQueryParameter] ?string $genre = null,    
        #[MapQueryParameter] ?string $year = null,     
        #[MapQueryParameter] ?string $order = null,  
        #[MapQueryParameter] int $page = 1,
    ): Response
    {
        $hasTitleSearch = $title !== null && trim($title) !== '';
        $queryParameters = [];

        if ($hasTitleSearch) {
            $queryParameters['title'] = trim($title);
        } else {
            if ($genre !== null && trim($genre) !== '') {
                $queryParameters['genre'] = $genre;
            }

            if ($year !== null && trim($year) !== '') {
                $queryParameters['year'] = trim($year);
            }

            if ($order !== null && trim($order) !== '') {
                $queryParameters['order'] = $order;
            }
        }

        if ($page < 1) {
            return $this->redirectToRoute('app_home', $queryParameters);
        }

        // $movies = $movieRepository->findAll();

        //dd($movies);

//        return $this->render('home/index.html.twig', [
//            'controller_name' => 'HomeController',
//            'movies' => $movies
//        ]);

        //$movies = $movieRepository->findAll();


        /* if ($genre !== null) {

            $genreEntity = $genreRepository->findOneByName($genre);           

            if ($genreEntity !== null) {
                
                $movies = $genreEntity->getMovies()->toArray();              

                if ($year !== null) {

                    $moviesForYear = [];

                    for ($i = 0; $i < count($movies); $i++) {                     
                        if ($movies[$i]->getReleaseYear() === (int) $year) {
                            $moviesForYear[] = $movies[$i];
                        }
                    }                    

                    $movies = $moviesForYear;
                                        
                }

            } else {
                $movies = [];
            }

            
        } elseif ($year !== null) {
            $movies = $movieRepository->findBy([
                'releaseYear' => (int) $year,
            ]);
        } else {
            $movies = $movieRepository->findAllWithGenres($title);
        } */

        $moviesPage = $movieRepository->findFilteredMovies(
            $title,
            $genre,
            $year,
            $order,
            $page,
            self::MOVIES_PER_PAGE
        );

        if ($page > $moviesPage->getPageCount()) {
            return $this->redirectToRoute('app_home', $queryParameters);
        }

        $releaseYears = [];

        if (!$hasTitleSearch) {
            $releaseYears = $movieRepository->findReleaseYears(); // pobieramy filtry (rok/lata) tylko jeśli nie podano tytułu (zgodne z założeniami)
        }

        $genres = $genreRepository->findAll(); // potrzebne do stworzenia dynamicznej listy gatunków w base.html.tiwg 
        // dump($genres);
        // exit();

        // dump($genre);
        // exit();



        return $this->render('home/index.html.twig', [
            'movies' => $moviesPage->getItems(),
            'genres' => $genres,
            'releaseYears' => $releaseYears,
            'currentPage' => $page,
            'totalPages' => $moviesPage->getPageCount(),
            'queryParameters' => $queryParameters,
            // 'order' => $order,
        ]);
    }
}
