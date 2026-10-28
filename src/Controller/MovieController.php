<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieType;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Service\PosterUploader;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Entity\User;
use App\Entity\Review;
use App\Form\ReviewType;

use App\Service\MovieRatingService;

#[Route('/movie')]
final class MovieController extends AbstractController
{
    #[Route(name: 'app_movie_index', methods: ['GET'])]
    public function index(MovieRepository $movieRepository): Response
    {
        return $this->render('movie/index.html.twig', [
            'movies' => $movieRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_movie_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        PosterUploader $posterUploader
    ): Response
    {
        $movie = new Movie();
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $posterFile = $form->get('posterFile')->getData();

            try {
                if ($posterFile instanceof UploadedFile) {
                    $movie->setPosterPath(
                        $posterUploader->upload($posterFile)
                    );
                }

                $entityManager->persist($movie);
                $entityManager->flush();

                return $this->redirectToRoute(
                    'app_movie_index',
                    [],
                    Response::HTTP_SEE_OTHER
                );
            } catch (FileException $exception) {
                $form->get('posterFile')->addError(
                    new FormError('Nie udało się zapisać plakatu. Spróbuj ponownie.')
                );
            }

//            $entityManager->persist($movie);
//            $entityManager->flush();
//
//            return $this->redirectToRoute('app_movie_index', [], Response::HTTP_SEE_OTHER);

        }

        return $this->render('movie/new.html.twig', [
            'movie' => $movie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_movie_show', methods: ['GET', 'POST'])]
    public function show(
        Movie $movie,
        ReviewRepository $reviewRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        MovieRatingService $movieRatingService,
    ): Response
    {       
        $movieRatingData = $movieRatingService->calculateAverage($movie);     

        $reviews = $movieRatingData["reviews"]; 
        $count = $movieRatingData["count"]; 
        $commentsCount = $movieRatingData["commentsCount"]; 
        $ratingAverage = $movieRatingData["ratingAverage"]; // 6.7
        $starDisplay = $movieRatingData["starDisplay"]; // 6.5
        $decimalPart = $movieRatingData["decimalPart"]; // 0.5
        $remainingStars = $movieRatingData["remainingStars"]; // 3.0

        // dump($ratingAverage);   
        // dump($starDisplay);
        // dump($decimalPart);
        // dump($remainingStars);
        // dump($values);
        // exit();

        // Sprawdzenie, czy zalogowany user dodał już recenzję do tego filmu (w celu wyświetleni/ukrycia formularza dodawania recenzji na stronie szczegółów filmu) -->

        // $userReview = $reviewRepository->findOneBy([
        //     'user' => $this->getUser(),
        //     'movie' => $movie,
        // ]);

        $user = $this->getUser();
        $userReview = null;
        $reviewForm = null;

        if ($user instanceof User) {

            $userReview = $reviewRepository->findOneBy([
                'user' => $user,
                'movie' => $movie,
            ]);

            //dump($userReview); exit(); // może być Encją lub nullem

            if ($userReview === null) {
                $review = new Review();
                $review->setUser($user);
                $review->setMovie($movie);

                $form = $this->createForm(ReviewType::class, $review);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager->persist($review);
                    $entityManager->flush();

                    return $this->redirectToRoute('app_movie_show', [
                        'id' => $movie->getId(),
                    ]);
                }

                $reviewForm = $form->createView();
            }
        }


        

        return $this->render('movie/show.html.twig', [
            'movie' => $movie,
            'ratingAverage' => $ratingAverage,
            'starDisplay' => $starDisplay,
            'decimalPart' => $decimalPart,
            'remainingStars' => $remainingStars,
            'count' => $count,
            'commentsCount' => $commentsCount,
            'reviews' => $reviews,
            'userReview' => $userReview,
            'reviewForm' => $reviewForm,
            
            
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_movie_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Movie $movie,
        EntityManagerInterface $entityManager,
        PosterUploader $posterUploader
    ): Response
    {
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $posterFile = $form->get('posterFile')->getData();

            try {
                if ($posterFile instanceof UploadedFile) {
                    $movie->setPosterPath(
                        $posterUploader->upload($posterFile)
                    );
                }

                //$entityManager->persist($movie);
                $entityManager->flush();

                return $this->redirectToRoute(
                    'app_movie_index',
                    [],
                    Response::HTTP_SEE_OTHER
                );
            } catch (FileException $exception) {
                $form->get('posterFile')->addError(
                    new FormError('Nie udało się zapisać plakatu. Spróbuj ponownie.')
                );
            }

//            $entityManager->flush();
//
//            return $this->redirectToRoute('app_movie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('movie/edit.html.twig', [
            'movie' => $movie,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_movie_delete', methods: ['POST'])]
    public function delete(Request $request, Movie $movie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$movie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($movie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_movie_index', [], Response::HTTP_SEE_OTHER);
    }
}
