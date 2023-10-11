<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
     * Permet d'ajouter une annonce à la bdd
     *
     * @return Response
     */
    #[Route("/ads/new", name:"ads_create")]
    public function create(): Response
    {
        // Méthode avec la création direct du bouton dans la création du form
        // $ad = new Ad();
        // $form = $this->createFormBuilder($ad) // créera notre formulaire
        //              ->add('title')
        //              ->add('introduction')
        //              ->add('content')
        //              ->add('rooms')
        //              ->add('price')
        //              ->add('save', SubmitType::class,[
        //                 'label' => "Créer la nouvelle annonce",
        //                 'attr' => [
        //                     'class' => "btn btn-primary"
        //                 ]
        //              ]) // pas un champ, on doit spécifier de quoi il s'agit et importer la class (bouton submit) et on peut lui donner un tableau d'option
        //              ->getForm();

        $ad = new Ad();
        $form = $this->createForm(AnnonceType::class, $ad); // formulaire qu'on a créer de façon externe ( externaliser ) Voir Form/AnnonceType

        // Formulaire de manière interne ( internaliser )
        // $form = $this->createFormBuilder($ad) // créera notre formulaire
        //              ->add('title')
        //              ->add('introduction')
        //              ->add('content')
        //              ->add('rooms')
        //              ->add('price')
        //              ->getForm();
        

        return $this->render("ad/new.html.twig",[
            'myForm' => $form->createView() // créera la vue de notre formulaire
        ]);
    }

    /**
     * Permet d'afficher une annonce
     * @param string $slug
     * @param Ad $ad
     * @return Response
     */
    #[Route("/ads/{slug}", name:"ads_show")]
    public function show(string $slug, Ad $ad):Response
    {
        // $ad = $repo->findOneBy(["slug"=>$slug]) // mais symfony flex permet de le faire automatiquement (il vérifie par lui même)

        dump($ad);// permet de savoir ce qu'on récupère (s'affiche dans la barra symfo)

        return $this->render('ad/show.html.twig', [
            'ad' => $ad
        ]);
    }
}
