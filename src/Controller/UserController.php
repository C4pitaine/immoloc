<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * Permet d'afficher le profil d'un utilisateur en utilisant son slug
     * 
     * @return Response
     */
    #[Route('/user/{slug}', name: 'user_show')]
    public function index(User $user): Response
    {
        return $this->render('user/index.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * Permet d'afficher le profil de l'utilisateur connecté ( son propre profil )
     *
     * @return Response
     */
    #[Route("/account", name:"account_index")]
    public function myAccount(): Response
    {       
        return $this->render('user/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }
}
