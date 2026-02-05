<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\RateLimiter\Annotation\RateLimiter;
use App\Service\PrismicService;


class ActualitesController extends AbstractController
{

    #[Route('/actualites', name: 'app_actualites')]
    #[RateLimiter('content_scraping')]
    public function index(Request $request, PrismicService $prismic): Response
    {
        $page = $request->query->getInt('page', 1);
        $category = $request->query->get('category');
        $articlesData = $prismic->getArticles($page, 12, $category);

        return $this->render('actualites/index.html.twig', [
            'articles' => $articlesData['results'],
            'total_pages' => $articlesData['total_pages'],
            'current_page' => $page,
            'active_category' => $category
        ]);
    }


    #[Route('/actualites/{slug}', name: 'app_actualites_show')]
    #[RateLimiter('content_scraping')]
    public function show(string $slug, PrismicService $prismic): Response
    {
        $article = $prismic->getDocument('article', $slug);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        return $this->render('actualites/show.html.twig', [
            'article' => $article
        ]);
    }
}
