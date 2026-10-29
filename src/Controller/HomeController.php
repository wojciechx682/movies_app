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
        // #[MapQueryParameter] ?string $order = null,
        #[MapQueryParameter] string $title = '',
        #[MapQueryParameter] ?string $genre = null,        
    ): Response
    {
        // $movies = $movieRepository->findAll();

        //dd($movies);

//        return $this->render('home/index.html.twig', [
//            'controller_name' => 'HomeController',
//            'movies' => $movies
//        ]);

        //$movies = $movieRepository->findAll();


        if ($genre !== null) {

            $genreEntity = $genreRepository->findOneByName($genre);           

            if ($genreEntity !== null) {
                // dump($genreEntity);
                // exit();
                    //$movies = $movieRepository->findByGenres($genreEntity); // ERROR "assert($assoc !== null)"
                $movies = $genreEntity->getMovies()->toArray();

                // dump($movies);
                // exit();
            } else {
                $movies = [];
            }

            // $movies = $movieRepository->findByGenres([
            //     'genres' => $genre,
            // ]);

            // dump($movies);
            // exit();
        } else {
            $movies = $movieRepository->findAllWithGenres($title);
        }

        

        $genres = $genreRepository->findAll(); // potrzebne do stworzenia dynamicznej listy gatunków w base.html.tiwg
        // dump($genres);
        // exit();

        // dump($genre);
        // exit();



        return $this->render('home/index.html.twig', [
            'movies' => $movies,
            'genres' => $genres,
            // 'order' => $order,
        ]);
    }
}
