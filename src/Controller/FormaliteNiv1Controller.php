<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormaliteNiv1Controller extends AbstractController
{
    #[Route('/formalites/creation', name: 'app_formalite_creation')]
    public function index(): Response
    {
        return $this->render('formalite_niv1/index.html.twig');
    }
}
