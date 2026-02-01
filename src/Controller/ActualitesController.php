<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller for the news/blog section.
 */
class ActualitesController extends AbstractController
{
    /**
     * Renders the news list page.
     *
     * @return Response
     */
    #[Route('/actualites', name: 'app_actualites')]
    public function index(): Response
    {
        return $this->render('actualites/index.html.twig');
    }
}
