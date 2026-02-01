<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormalitesController extends AbstractController
{
    #[Route('/formalites', name: 'app_formalites')]
    public function index(): Response
    {
        return $this->render('formalites/index.html.twig');
    }
}
