<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\TicketManagerType;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ManagerController
 * @package App\Controller
 * @IsGranted ("ROLE_MANAGER")
 */
class ManagerController extends AbstractController
{
    /**
     * @param UserRepository $userRepository
     * @param TicketRepository $ticketRepository
     * @param UserInterface $user
     * @return Response
     */
    #[Route('/manager', name: 'manager_index', methods: ['GET', 'POST'])]
    public function dashboard(UserRepository $userRepository, TicketRepository $ticketRepository, UserInterface $user): Response
    {
        if($_POST !== null)
        {
            //todo Replace with and add logic to properly handle form
            /** @var Ticket $ticket $ticket */
            $ticket = $_POST['ticket'];
            $ticket->setPriority($_POST['priority']);
            $ticket->setPriority($_POST['agent']);

        }

        /** @var User $user */
        $tickets = $ticketRepository->findAll();
        $assignedTickets = [];
        $unassignedTickets = [];

        foreach ($tickets as $ticket)
        {
            if($ticket->getAssignedAgent() === null)
            {
                $unassignedTickets[] = $ticket;
            }else
            {
                $assignedTickets[] = $ticket;
            }
        }

        $agents = $userRepository->findBy(['agentLevel' => !null]);
        $totalOpen = count($unassignedTickets);
        $totalClosed = 0;
        $totalReopened = 0;

        foreach ($agents as $agent)
        {
            $totalClosed += $agent->getClosedTickets();
            $totalReopened += $agent->getReopenedTickets();
        }

        return $this->render('manager/index.html.twig', [
            'openTickets' => $totalOpen,
            'closedTickets' => $totalClosed,
            'reopened'=> $totalReopened,
            'ratio'=> round($totalReopened/$totalClosed,2),
            'assignedTickets' => $assignedTickets,
            'unassignedTickets' => $unassignedTickets,
            'agents' => $agents,
        ]);
    }

    #[Route('/un-assign-all', name: 'unassign_all')]
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

        return $this->redirectToRoute('manager_index');

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
