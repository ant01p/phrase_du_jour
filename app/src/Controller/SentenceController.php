<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Sentence;
use App\Entity\Comment;
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
    public function index(SentenceRepository $sentences): Response
    {
        $sentences = $sentences->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'SentenceController',
            'sentences' => $sentences,
        ]);
    }

    #[Route('/sentence/{id}', name: 'app_sentence_show')]
    public function show($id, SentenceRepository $sentenceRepo, CommentRepository $commentRepo, Request $request, EntityManagerInterface $entityManager): Response
    {
        $sentence = $sentenceRepo->find($id);

        $comments = $commentRepo->findBy(
            ['sentence' => $sentence],
            ['createdAt' => 'DESC']
        );

        $comment = new Comment();
        $commentForm = null;

        if ($this->getUser()) {
            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);
            $commentForm = $form;

            if ($form->isSubmitted() && $form->isValid()) {
                $comment->setSentence($sentence);
                $comment->setCreatedAt(new \DateTimeImmutable());
                $comment->setAuthor($this->getUser());
                $entityManager->persist($comment);
                $entityManager->flush();

                return $this->redirectToRoute('app_sentence_show', ['id' => $sentence->getId()]);
            }
        }

        return $this->render('sentence/show.html.twig', [
            'sentence' => $sentence,
            'comments' => $comments,
            'commentForm' => $commentForm ? $commentForm->createView() : null,
        ]);
    }

    #[Route('/sentence/{id}/like', name: 'app_sentence_like', methods: ['POST'])]
    public function like(Sentence $sentence, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('like' . $sentence->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
        
        $sentence->setLikes($sentence->getLikes() + 1);

        $entityManager->flush();

        return $this->redirectToRoute('app_sentence_show', [
            'id' => $sentence->getId(),
        ]);
    }

}

