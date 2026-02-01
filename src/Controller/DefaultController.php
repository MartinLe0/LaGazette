<?php

namespace App\Controller;

use App\Service\PrismicService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller for handling default routes and Prismic integration.
 */
class DefaultController extends AbstractController
{
    /**
     * Renders the legal notices page.
     *
     * @return Response
     */
    #[Route('/annonces-legales', name: 'app_annonces_legales')]
    public function annoncesLegales(): Response
    {
        return $this->render('annonces_legales/index.html.twig');
    }

    /**
     * Renders a CMS page from Prismic based on its slug.
     *
     * @param PrismicService $prismic The Prismic service
     * @param string $slug The page slug (defaults to 'home')
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If the page is not found
     */
    #[Route('/cms', name: 'app_cms')]
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

    /**
     * Webhook endpoint for Prismic to clear the local cache.
     *
     * @param Request $request The incoming request
     * @param PrismicService $prismic The Prismic service
     * @return Response
     */
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
