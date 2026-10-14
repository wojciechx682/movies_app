<?php

namespace App\Controller;

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
        #[MapQueryParameter] ?string $order = null,
    ): Response
    {
        // $movies = $movieRepository->findAll();

        //dd($movies);

//        return $this->render('home/index.html.twig', [
//            'controller_name' => 'HomeController',
//            'movies' => $movies
//        ]);

        $movies = $movieRepository->findAll();

        return $this->render('home/index.html.twig', [
            'movies' => $movies,
            'order' => $order,
        ]);
    }
}
