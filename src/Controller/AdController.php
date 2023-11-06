<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
    public function create(Request $request ,EntityManagerInterface $manager): Response
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

        // permet de voir si les images s'affiche correctement ou de rentrer des images prédéfinis
        // $image1 = new Image();
        // $image1->setUrl("https://picsum.photos/400/200")
        //        ->setCaption('Titre 1');

        // $ad->addImage($image1);

        // $image2 = new Image();
        // $image2->setUrl("https://picsum.photos/400/200")
        //        ->setCaption('Titre 2');
        
        // $ad->addImage($image2);


        $form = $this->createForm(AnnonceType::class, $ad); // formulaire qu'on a créer de façon externe ( externaliser ) Voir Form/AnnonceType

        // Formulaire de manière interne ( internaliser )
        // $form = $this->createFormBuilder($ad) // créera notre formulaire
        //              ->add('title')
        //              ->add('introduction')
        //              ->add('content')
        //              ->add('rooms')
        //              ->add('price')
        //              ->getForm();

        //$arrayForm = $request->request->all(); // lorsqu'on appuyera sur le bouton pour envoyer le form , les infos seront stocké dans $arrayForm
        $form->handleRequest($request); // l'évènement de la requête ( si mon form a été envoyé ou non) 

        if($form->isSubmitted() && $form->isValid()) // permet de savoir si le formulaire a été soumis et validé
        {
            // traitement des images associées à l'annonce
            foreach($ad->getImages() as $image)
            {
                $image->setAd($ad);
                $manager->persist($image);
            }

            // intégration de l'user
            $ad->setAuthor($this->getUser());

            //je persist mon objet $ad
            $manager->persist($ad);
            //j'envois les persistences dans ma bdd
            $manager->flush();

            $this->addFlash('success', "L'annonce <strong>".$ad->getTitle()."</strong> a bien été enregistrée"); // permet de créer un message flash, 2 paramètre : son titre et le message

            return $this->redirectToRoute('ads_show',[ // $this->redirectToRoute équivaut à notre header location, il a besoin de la route en question ( et ads_show a besoin d'un paramètre le {slug})
                'slug' => $ad->getSlug() // on vient directement chercher le getSlug() de l'objet qu'on vient de créer
            ]);
        }
        

        return $this->render("ad/new.html.twig",[
            'myForm' => $form->createView() // créera la vue de notre formulaire
        ]);
    }

    
    
    /**
     * Permet d'editer une annonce
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param Ad $ad
     * @return Response
     */
    #[Route("ads/{slug}/edit", name:"ads_edit")]
    public function edit(Request $request,EntityManagerInterface $manager, Ad $ad): Response
    {
        // il ne fait pas de nouvelle instanciation (on ne créer pas un nouvel objet) on vient faire une injection de dépendance pour remplir $ad pour récupérer notre objet
        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        //si je veux que le slug soit automatique -> on a créer une fonction qui slug le title si le slug est vide, dans le cas de la modif on doit le mettre vide si on veut le changer
        //$ad->setSlug("");

        // on vient vérifier si le formulaire a été envoyé
        if($form->isSubmitted() && $form->isValid())
        {
            //gestion des images
            foreach($ad->getImages() as $image)
            {
                $image->setAd($ad);
                $manager->persist($image);
            }
            $manager->persist($ad);
            $manager->flush();

            //message flash
            $this->addFlash(
                'success',
                "L'annonce <strong>".$ad->getTitle()."</strong> a bien été modifiée !"
            );

            // on renvoit vers l'annonce avec le message flash pour la modif
            return $this->redirectToRoute('ads_show',[
                'slug' => $ad->getSlug()
              ]);
        }
        return $this->render("ad/edit.html.twig",[
            "ad" => $ad,
            "myForm" => $form->createView()
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
