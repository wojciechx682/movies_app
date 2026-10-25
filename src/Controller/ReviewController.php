<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/review')]
final class ReviewController extends AbstractController
{
    #[Route(name: 'app_review_index', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepository): Response
    {       
        return $this->render('review/index.html.twig', [
            'reviews' => $reviewRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'app_review_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] User $user,
    ): Response
    {
        $review = new Review();
        $review->setUser($user); // currently logged-in User

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('app_review_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('review/new.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_review_show', methods: ['GET'])]
    public function show(Review $review): Response
    {
        return $this->render('review/show.html.twig', [
            'review' => $review,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/edit', name: 'app_review_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        if ($review->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException(
                'You can edit only your own reviews.'
            );
        }

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $review->setUpdatedAt(new \DateTimeImmutable()); // Review edit date
            $entityManager->flush();

            //return $this->redirectToRoute('app_review_index', [], Response::HTTP_SEE_OTHER);

            $movie = $review->getMovie();

            return $this->redirectToRoute('app_movie_show', ['id' => $movie->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('review/edit.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'app_review_delete', methods: ['POST'])]
    public function delete(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        if ($review->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException(
                'You can delete only your own reviews.'
            );
        }

        if ($this->isCsrfTokenValid('delete'.$review->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($review);
            $entityManager->flush();
        }

        $movie = $review->getMovie();

        return $this->redirectToRoute('app_movie_show', ['id' => $movie->getId()], Response::HTTP_SEE_OTHER);
    }
}
