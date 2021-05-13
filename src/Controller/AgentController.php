<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\Ticket;
use App\Form\CommentType;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/agent')]
class AgentController extends AbstractController
{
    #[Route('/', name: 'agent_ticket_index', methods: ['GET'])]
    public function showAllOpenTickets(TicketRepository $ticketRepository, UserInterface $user): Response
    {
        /** @var User $user */
        $id = $user->getId();
        $level = $user->getAgentLevel();
        return $this->render('agent/index.html.twig', [
            'open_tickets' => $ticketRepository->findBy(['status' => 1, 'level'=> $level],['priority'=>'ASC']),
            'assigned_tickets' => $ticketRepository->findBy(['assignedAgent'=> $id],['priority'=>'ASC'])
        ]);
    }

    #[Route('/assign/{id}/{agent}', name: 'agent_assign', methods: ['POST'])]
    public function assignTicket(TicketRepository $ticketRepository,Request $request , Ticket $ticket, User $agent): Response
    {
        if ($this->isCsrfTokenValid('assign' . $ticket->getId(), $request->request->get('_token'))) {

            $ticket->setAssignedAgent($agent);
            $ticket->setStatus(2);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->flush();

        }
        return $this->redirectToRoute('agent_ticket_index');
    }

    #[Route('/escalate/{id}', name: 'ticket_escalate', methods: ['POST'])]
    public function escalateTicket(Request $request , Ticket $ticket, UserInterface $user): Response
    {
        if ($this->isCsrfTokenValid('escalate' . $ticket->getId(), $request->request->get('_token'))) {

            /** @var User $user */
            $escalated = $user->getEscalatedTickets();

            $user->setEscalatedTickets( ++$escalated);
            $ticket->setAssignedAgent(null);
            $ticket->setStatus(1);
            $ticket->setLevel(2);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->persist($user);
            $entityManager->flush();
        }
        return $this->redirectToRoute('agent_ticket_index');
    }



    #[Route('/{id}', name: 'ticket_show', methods: ['GET'])]
    public function show(Request $request, Ticket $ticket): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

        }
        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
            'comment' => $comment,
            'form' => $form->createView()]);
    }

    #[Route('/{id}/edit', name: 'ticket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ticket $ticket): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ticket_index');
        }

        return $this->render('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

}
