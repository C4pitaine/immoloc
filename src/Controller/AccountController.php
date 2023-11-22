<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Form\ImgModifyType;
use App\Entity\UserImgModify;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

class AccountController extends AbstractController
{
    /**
     * Permet à l'utilisateur de se connecter
     *
     * @param AuthenticationUtils $utils
     * @return Response
     */
    #[Route('/login', name: 'account_login')]
    public function index(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername(); // on donnera en required value à l'input username le dernier username remplit pour qu'il le remplisse automatiquement si il a une erreur ( dans le nom ou le mdp )

        $loginError = null;

        if($error instanceof TooManyLoginAttemptsAuthenticationException)
        {
            // l'erreur est dû à la limitation de tentative de connexion
            $loginError = "Trop de tentatives de connexion. Réessayer plus tard";
        }

        return $this->render('account/index.html.twig', [
            'hasError' => $error !== null,
            'username' => $username,
            'loginError' => $loginError
        ]);
    }

    /**
     * Permet de se déconnecter
     *
     * @return void
     */
    #[Route("/logout", name: "account_logout")]
    public function logout(): void
    {

    }

    /**
     * Permet d'afficher le fomulaire d'inscription ainsi que la gestion de l'inscription de l'utilisateur
     *
     * @param Request $request
     * @param EntityManagerInterface $manger
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route("/register", name:"account_register")]
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //gestion de l'image
            $file = $form['picture']->getData(); // récupère les information de l'image
            if(!empty($file))
            {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename); // permet avec le premier paramètre de donner des informations sur comment gérer mes éléments, Any-Latin enlève les caractères spéciaux
                $newFilename = $safeFilename."-".uniqid().'.'.$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('uploads_directory'), //où on va l'envoyer
                        $newFilename // qui on envoit
                    );
                }catch(FileException $e)
                {
                    return $e->getMessage();
                }

                $user->setPicture($newFilename);
            }


            // gestion de l'inscription dans la bdd
            $hash = $hasher->hashPassword($user, $user->getPassword()); // permet de hasher le password
            $user->setPassword($hash); // on modifie le mot de passe pour lui donner le crypter

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('account_login');
        }

        return $this->render("account/registration.html.twig",[
            'myForm' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier un utilisateur
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/account/profile', name:"account_profile")]
    #[IsGranted('ROLE_USER')]
    public function profile(Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();// permet de récup l'utilisateur connecté 

        // pour la validation des immages ( plus tard validation groups )
        $fileName = $user->getPicture();
        if(!empty($fileName))
        {
            $user->setPicture(
                new File($this->getParameter('uploads_directory').'/'.$user->getPicture())
            );
        }


        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $user->setSlug('')
                 ->setPicture($fileName);
             
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les données ont été enregistrées avec succès"
            );

            return $this->redirectToRoute('account_index');
        }
        

        return $this->render("account/profile.html.twig",[
            'myForm' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier le mot de passe
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route("/account/password-update", name:"account_password")]
    #[IsGranted('ROLE_USER')]
    public function updatePassword(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $passwordUpdate = new PasswordUpdate();
        $user = $this->getUser();
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // vérifier si le nouveau mot de passe correspond à l'ancien
            if(!password_verify($passwordUpdate->getOldPassword(),$user->getPassword()))//$user->getPassword() récupérer le mot de passe de notre bdd
            {
                //gestion de l'erreur
                $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez tapé n'est pas votre mot de passe actuel"));
            }else{
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $hasher->hashPassword($user, $newPassword);

                $user->setPassword($hash);

                $manager->persist($user);
                $manager->flush();
                
                $this->addFlash(
                    'success',
                    'Votre mot de passe a bien été modifié'
                );

                return $this->redirectToRoute('account_index');
            }
            
           
        }


        return $this->render("account/password.html.twig", [
            'myForm' => $form->createView()
        ]);
    }

    /**
     * Permet de supprimer l'image utilisateur
     *
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/account/delimg", name:"account_delimg")]
    #[IsGranted('ROLE_USER')]
    public function removeImg(EntityManagerInterface $manager):Response
    {
        $user = $this->getUser();
        if(!empty($user->getPicture()))
        {
            unlink($this->getParameter('uploads_directory').'/'.$user->getPicture());
            $user->setPicture('');
            $manager->persist($user);
            $manager->flush();
            $this->addFlash(
                'success',
                'Votre avatar a bien été supprimé'
            );
        }

        return $this->redirectToRoute('account_index');
    }

    /**
     * Permet de modifier l'avatar de l'utilisateur
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/account/imgmodify", name:"account_modifimg")]
    #[IsGranted('ROLE_USER')]
    public function imgModify(Request $request, EntityManagerInterface $manager):Response
    {
        $imgModify = new UserImgModify();
        $user = $this->getUser();
        $form = $this->createForm(ImgModifyType::class, $imgModify);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //permet de supprimer l'image dans le dossier
            //gestion de la non-obligatoire de l'image
            if(!empty($user->getPicture()))
            {
                unlink($this->getParameter('uploads_directory').'/'.$user->getPicture());
            }

            //gestion de l'image
            $file = $form['newPicture']->getData(); // récupère les information de l'image
            if(!empty($file))
            {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename); // permet avec le premier paramètre de donner des informations sur comment gérer mes éléments, Any-Latin enlève les caractères spéciaux
                $newFilename = $safeFilename."-".uniqid().'.'.$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('uploads_directory'), //où on va l'envoyer
                        $newFilename // qui on envoit
                    );
                }catch(FileException $e)
                {
                    return $e->getMessage();
                }

                $user->setPicture($newFilename);
            }

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre avatar a bien été modifié'
            );

            return $this->redirectToRoute('account_index');
        }

        return $this->render("account/imgModify.html.twig",[
            'myForm' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher les réservations de l'utilisateur
     *
     * @return Response
     */
    #[Route("/account/booking", name:"account_booking")]
    #[IsGranted('ROLE_USER')]
    public function bookings(): Response
    {
        return $this->render("account/bookings.html.twig");
    }
}
