<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminBookingController extends AbstractController
{
    /**
     * Permet d'afficher toutes les réservations
     *
     * @param BookingRepository $repo
     * @return Response
     */
    #[Route('/admin/bookings', name: 'admin_bookings_index')]
    public function index(BookingRepository $repo): Response
    {
        return $this->render('admin/booking/index.html.twig', [
            'bookings' => $repo->findAll()
        ]);
    }

    /**
     * Permet de modifier une réservation
     *
     * @param Booking $booking
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/booking/{id}/edit', name:'admin_bookings_edit')]
    public function edit(Booking $booking,Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AdminBookingType::class,$booking,[
            'validation_groups' => ['Default']
        ]); // on peut donner un tableau d'option en 3ème param
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $booking->setAmount(0); // pour qu'il refasse le calcul dans l'entité Booking

            $manager->persist($booking);
            $manager->flush();

            $this->addFlash('success','La réservation n° <strong>'.$booking->getId().'</strong>');
        }

        return $this->render("admin/booking/edit.html.twig",[
            "booking" => $booking,
            "myForm" => $form->createView()
        ]);
        
    }
}
