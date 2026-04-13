<?php

namespace App\Controller;

use App\Repository\SentenceRepository;
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

}