<?php

namespace App\Controller;

use App\Service\PrismicService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[Route('/page/{slug}', name: 'app_page')]
    public function index(PrismicService $prismic, string $slug = 'home'): Response
    {
        // For simple vitrine, we treat "/" as "page/home"
        $document = $prismic->getDocument('page', $slug);

        if (!$document) {
            throw $this->createNotFoundException('Page not found');
        }

        return $this->render('base.html.twig', [
            'content' => $document['data'],
            'meta' => [
                'type' => $document['type'],
                'last_updated' => $document['last_publication_date'],
            ]
        ]);
    }

    #[Route('/webhook/prismic', name: 'app_prismic_webhook', methods: ['POST'])]
    public function webhook(Request $request, PrismicService $prismic): Response
    {
        $secret = $request->headers->get('X-Prismic-Secret');
        $expectedSecret = $this->getParameter('kernel.secret'); // Or a specific secret

        // Better to use a specific secret for Prismic webhooks
        // if ($secret !== $this->getParameter('PRISMIC_WEBHOOK_SECRET')) { ... }

        $prismic->clearCache();

        return new Response('Cache cleared', Response::HTTP_OK);
    }
}
