<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\RateLimiter\Annotation\RateLimiter;

/**
 * Controller for the project's homepage.
 */
class AccueilController extends AbstractController
{
    /**
     * Renders the homepage.
     *
     * @return Response
     */
    #[Route('/', name: 'app_accueil')]
    #[RateLimiter('content_scraping')]
    public function index(): Response
    {
        return $this->render('accueil/index.html.twig');
    }
}
