<?php

namespace App\Controller;

use App\Service\PrismicService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\RateLimiter\Annotation\RateLimiter;


class PageController extends AbstractController
{

    #[Route('/cms', name: 'app_cms')]
    #[Route('/page/{slug}', name: 'app_page', requirements: ['slug' => '[a-z0-9\-]+'])]
    #[RateLimiter('content_scraping')]
    public function index(PrismicService $prismic, string $slug = 'home'): Response
    {
        $document = $prismic->getDocument('page', $slug);

        if (!$document) {
            throw $this->createNotFoundException('Page not found');
        }

        return $this->render('accueil_base.html.twig', [
            'content' => $document['data'],
            'meta' => [
                'type' => $document['type'],
                'last_updated' => $document['last_publication_date'],
            ]
        ]);
    }


    #[Route('/webhook/prismic', name: 'app_prismic_webhook', methods: ['POST'])]
    #[RateLimiter('webhook_api')]
    public function webhook(Request $request, PrismicService $prismic): Response
    {
        $secret = $request->headers->get('X-Prismic-Secret');
        $expectedSecret = $this->getParameter('kernel.secret');

        if ($this->getParameter('app.prismic_webhook_secret')) {
            $expectedSecret = $this->getParameter('app.prismic_webhook_secret');
        }

        if ($secret !== $expectedSecret) {
            throw new AccessDeniedHttpException('Invalid webhook secret');
        }

        $prismic->clearCache();

        return new Response('Cache cleared', Response::HTTP_OK);
    }
}
