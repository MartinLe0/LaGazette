<?php

namespace App\Controller;

use App\Service\PrismicService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\RateLimiter\Annotation\RateLimiter;


class LegalController extends AbstractController
{

    #[Route('/annonces-legales', name: 'app_annonces_legales')]
    #[RateLimiter('content_scraping')]
    public function annoncesLegales(PrismicService $prismic): Response
    {
        $document = $prismic->getDocument('page', 'annonces-legales');

        return $this->render('annonces_legales/index.html.twig', [
            'content' => $document ? $document['data'] : null
        ]);
    }


    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    #[RateLimiter('content_scraping')]
    public function mentionsLegales(): Response
    {
        return $this->render('mentions_legales/index.html.twig');
    }


    #[Route('/politique-de-confidentialite', name: 'app_confidentialite')]
    #[RateLimiter('content_scraping')]
    public function politiqueConfidentialite(): Response
    {
        return $this->render('politique_confidentialite/index.html.twig');
    }


    #[Route('/charte-cookies', name: 'app_cookies')]
    #[RateLimiter('content_scraping')]
    public function charteCookies(): Response
    {
        return $this->render('charte_cookies/index.html.twig');
    }
}
