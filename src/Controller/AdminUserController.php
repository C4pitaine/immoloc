<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminAccountType;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    /**
     * Permet d'afficher les utilisateurs
     *
     * @param PaginationService $pagination
     * @param integer $page
     * @return Response
     */
    #[Route('/admin/user/{page<\d+>?1}', name: 'admin_user_index')]
    public function index(PaginationService $pagination,int $page): Response
    {
        $pagination->setEntityClass(User::class)
                   ->setPage($page)
                   ->setLimit(5);

        return $this->render('admin/user/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet de modifier certaines informations d'un User
     *
     * @param User $user
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    #[Route("/admin/user/{id}/edit", name:"admin_user_edit")]
    public function edit(User $user,EntityManagerInterface $manager, Request $request): Response
    {
        $form = $this->createForm(AdminAccountType::class,$user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success',"Le profil de ".$user->getFullName()." a bien été modifié");
        }

        return $this->render("admin/user/edit.html.twig", [
            "user" => $user,
            "myForm" => $form->createView()
        ]);
    }
}
