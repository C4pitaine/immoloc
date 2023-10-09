<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')] // on supprime le /home et on laisse / pour devenir notre page d'accueil du site
    public function index(): Response
    {
        return $this->render('home.html.twig', [
            
        ]);
    }
}
