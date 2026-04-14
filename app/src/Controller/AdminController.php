<?php

namespace App\Controller;

use App\Entity\Sentence;
use App\Form\SentenceType;
use App\Repository\SentenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(SentenceRepository $sentences): Response
    {
        $sentences = $sentences->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'sentences' => $sentences,
        ]);
    }

    #[Route('/admin/add', name: 'app_admin_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sentence = new Sentence();
        $sentence->setCreatedAt(new \DateTimeImmutable());
        $sentence->setLikes(0);

        $sentenceForm = $this->createForm(SentenceType::class, $sentence);
        $sentenceForm->handleRequest($request);

        if ($sentenceForm->isSubmitted() && $sentenceForm->isValid()) {
            $entityManager->persist($sentence);
            $entityManager->flush();

            $this->addFlash('success', 'La phrase du jour a bien été ajoutée.');

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/form.html.twig', [
            'sentenceForm' => $sentenceForm->createView(),
            'isEdit' => false,
        ]);
    }

    #[Route('/admin/edit/{id}', name: 'app_admin_edit')]
    public function edit(Sentence $sentence, Request $request, EntityManagerInterface $entityManager): Response
    {
        $sentenceForm = $this->createForm(SentenceType::class, $sentence);
        $sentenceForm->handleRequest($request);

        if ($sentenceForm->isSubmitted() && $sentenceForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La phrase du jour a bien été modifiée.');

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/form.html.twig', [
            'sentenceForm' => $sentenceForm->createView(),
            'isEdit' => true,
        ]);
    }
    
    #[Route('/admin/delete/{id}', name: 'app_admin_delete')]
    public function delete(Sentence $sentence, EntityManagerInterface $entityManager, Request $request): Response
    {
         

        $csrf = $request->query->get('token_csrf');

        if ($this->isCsrfTokenValid('delete_item' . $sentence->getId(), $token)) {

        $entityManager->remove($sentence);
        $entityManager->flush();

        $this->addFlash('success', 'La phrase du jour a bien été supprimée.');
        }

        return $this->redirectToRoute('app_admin');
    }

}