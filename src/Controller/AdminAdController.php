<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAdController extends AbstractController
{
    /**
     * Permet d'afficher l'ensemble des annonces
     *
     * @param AdRepository $repo
     * @return Response
     */
    #[Route('/admin/ads/{page<\d+>?1}', name: 'admin_ads_index')] // Route('/admin/ads/{page?1}', name: 'admin_ads_index', requirements:["page"=>"\d+"])
    public function index(AdRepository $repo, int $page): Response
    {
        //$ads = $repo->findBy([],[],5,0); // pas de critère (il donnera tout), pas d'order, limit de 5 , offset 0

        $limit = 10;
        $start = $page * $limit - $limit;
        //page 1 * limit = 10 - limit = 0 
        //page 2 * limit = 20 - limit = 10
        $total = count($repo->findAll());
        //3.1 = 4
        $pages = ceil($total/$limit); // arrondir au supérieur

        $ads = $repo->findBy([],[],$limit,$start);

        return $this->render('admin/ad/index.html.twig', [
            'ads' => $ads,
            'pages' => $pages,
            'page' => $page
        ]);
    }

    /**
     * Permet d'afficher le formumaire d'édition
     *
     * @param Ad $ad
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/admin/ads/{id}/edit", name: "admin_ads_edit")]
    public function edit(Ad $ad,Request $request,EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash('success',"L'annonce <strong>".$ad->getTitle()."</strong> a bien été modifiée");
        }

        return $this->render("admin/ad/edit.html.twig",[
            "ad" => $ad,
            "myForm" => $form->createView()
        ]);
    }

    /**
     * Permet de supprimer une annonce
     *
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/admin/ads/{id}/delete", name: "admin_ads_delete")]
    public function delete(Ad $ad,EntityManagerInterface $manager): Response
    {
        // on ne peut pas supprimer une annonce qui possède des réservations
        if(count($ad->getBookings())>0)
        {
            $this->addFlash('warning', "Vous ne pouvez pas supprimer l'annonce <strong>".$ad->getTitle()."</strong> car elle possède des réservations");
        }else{
            $this->addFlash('success',"L'annonce <strong>".$ad->getTitle()."</strong> a bien été supprimée");
            
            $manager->remove($ad);
            $manager->flush();

        }

        return $this->redirectToRoute('admin_ads_index');
    }

}
