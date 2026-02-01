<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormaliteNiv2Controller extends AbstractController
{
    #[Route('/formalites/creation/fonds-physique', name: 'app_formalite_creation_fonds_physique')]
    public function index(): Response
    {
        return $this->render('formalite_niv2/index.html.twig');
    }
}
