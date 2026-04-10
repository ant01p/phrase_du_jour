<?php

namespace App\Controller;

use App\Repository\SentenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(SentenceRepository $sentenceRepository): Response
    {
        $sentences = $sentenceRepository->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'sentences' => $sentences,
        ]);
    }
}
