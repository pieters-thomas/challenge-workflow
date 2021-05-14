<?php

namespace App\Controller;

use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ManagerController extends AbstractController
{
    #[Route('/manager', name: 'manager')]
    public function index(): Response
    {
        return $this->render('manager/index.html.twig', [
            'controller_name' => 'ManagerController',
        ]);
    }

    #[Route('/un-assign-all/', name: 'unassign_all')]
    public function unAssignAll(TicketRepository $repository): Response
    {
        $tickets = $repository->findAll();
        $entityManager = $this->getDoctrine()->getManager();

        foreach ($tickets as $ticket)
        {
            if($ticket->getAssignedAgent() !== null && in_array($ticket->getStatus(), [2, 3], true))
            {
                $ticket->setAssignedAgent(null);
                $ticket->setStatus(1);
                $entityManager->persist($ticket);
            }
        }
        $entityManager->flush();

        return $this->redirectToRoute('manager');

    }
}
