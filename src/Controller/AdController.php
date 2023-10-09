<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Repository\AdRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
    /**
     * Permet d'afficher l'ensemble des annonces du site
     *
     * @param AdRepository $repo
     * @return Response
     */
    #[Route('/ads', name: 'ads_index')]
    public function index(AdRepository $repo): Response
    {

        $ads = $repo->findAll();
        return $this->render('ad/index.html.twig', [
            'ads' => $ads
        ]);
    }

    /**
     * Permet d'afficher une annonce
     * @param string $slug
     * @param Ad $ad
     * @return Response
     */
    #[Route("/ads{slug}", name:"ads_show")]
    public function show(string $slug, Ad $ad):Response
    {
        // $ad = $repo->findby(["slug"=>$slug]) // mais symfony flex permet de le faire automatiquement (il vérifie par lui même)
        return $this->render('ad/show.html.twig', [
            'ad' => $ad
        ]);
    }
}
