<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\RateLimiter\Annotation\RateLimiter;


class AccueilController extends AbstractController
{

    #[Route('/', name: 'app_accueil')]
    #[RateLimiter('content_scraping')]
    public function index(\App\Service\PrismicService $prismic): Response
    {
        $articlesData = $prismic->getArticles(1, 3);

        return $this->render('accueil/index.html.twig', [
            'articles' => $articlesData['results']
        ]);
    }
}
