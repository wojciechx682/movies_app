<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieType;
use App\Repository\MovieRepository;
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

    #[Route('/{id}', name: 'app_movie_show', methods: ['GET'])]
    public function show(Movie $movie): Response
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

        $ratingAverage = $count > 0 ? round($sum / $count, 1) : null;

        $starDisplay = $count > 0 ? round(($sum / $count) * 2) / 2 : null;

        $decimalPart = $starDisplay - ((int) floor($starDisplay));

        $remainingStars = 10 - ceil($starDisplay);        

        // dump($ratingAverage);   
        // dump($starDisplay);
        // dump($decimalPart);
        // exit();

        // Pobranie recenzji tego filmu -->

        

        return $this->render('movie/show.html.twig', [
            'movie' => $movie,
            'ratingAverage' => $ratingAverage,
            'starDisplay' => $starDisplay,
            'decimalPart' => $decimalPart,
            'remainingStars' => $remainingStars,
            'count' => $count,
            'commentsCount' => $commentsCount,
            'reviews' => $reviews,
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
