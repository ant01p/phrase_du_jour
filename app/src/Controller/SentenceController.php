<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Sentence;
use App\Form\CommentType;
use App\Repository\SentenceRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class SentenceController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('home/index.html.twig', [
            'sentences' => $entityManager->getRepository(Sentence::class)->findBy([], ['createdAt' => 'DESC'])
        ]);
    }

    #[Route('/sentence/{id}', name: 'app_sentence_show')]
    public function show(Sentence $sentence, CommentRepository $commentRepo, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comments = $commentRepo->findBy(
            ['sentence' => $sentence],
            ['createdAt' => 'DESC']
        );

        if ($this->getUser()) {
            $form = $this->createForm(CommentType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $comment = $form->getData();
                $comment->setCreatedAt(new \DateTimeImmutable());
                $comment->setAuthor($this->getUser());
                $comment->setSentence($sentence);

                $entityManager->persist($comment);
                $entityManager->flush();

                return $this->redirectToRoute('app_sentence_show', ['id' => $sentence->getId()]);
            }
        }

        return $this->render('sentence/show.html.twig', [
            'sentence' => $sentence,
            'comments' => $comments,
            'commentForm' => isset($form) ? $form->createView() : null,
        ]);
    }

    #[Route('/sentence/{id}/like', name: 'app_sentence_like')]
    public function like(Sentence $sentence, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('like' . $sentence->getId(), $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
        
        $sentence->setLikes($sentence->getLikes() + 1);

        $entityManager->flush();

        return $this->redirectToRoute('app_sentence_show', [
            'id' => $sentence->getId(),
        ]);
    }

}

