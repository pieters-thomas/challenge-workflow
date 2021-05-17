<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\TicketManagerType;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/will-not-fix/', name: 'will-not-fix', methods: ['post'])]
    public function Deny (Ticket $ticket, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUserId($user);
            $comment->setTicketId($ticket);

            if (!$comment->getPrivate()) {
                $ticket->setStatus(5);
                $ticket->setAssignedAgent(null);
                $ticket->setClosedBy($user);
                $ticket->setClosed(date_create(date("Y-m-d H:i:s")));
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('manager');
        }
        return $this->render('manager/show.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView()]);

    }

    #[Route('/ticket/{id}/edit', name: 'manager_edit_ticket', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ticket $ticket): Response
    {
        $form = $this->createForm(TicketManagerType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('manager');
        }

        return $this->render('manager/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

}
