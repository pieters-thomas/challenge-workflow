<?php

namespace App\Controller;


use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use PhpParser\Node\Expr\Array_;
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
    //#[Route('/manager', name: 'manager')]
    //public function index(): Response

    /**
     * @param TicketRepository $ticketRepository
     * @param UserInterface $user
     * @return Response
     */
    #[Route('/manager', name: 'manager_index', methods: ['GET', 'POST'])]
    public function dashboard(UserRepository $userRepository, TicketRepository $ticketRepository, UserInterface $user): Response
    {

        if($_POST !== null)
        {
            //todo Reaplace with and add logic to properly handle form
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


    public function getTicketsForAgent(TicketRepository $ticketRepository, UserInterface $user): array
    {
        $tickets_for_agent = [];
        /** @var User $user */
        $tickets = $ticketRepository->findall();
        foreach ( $tickets as $ticket ){
            /** @var Ticket $ticket */
            if ($ticket->getAssignedAgent() == $user)
                $tickets_for_agent[] = $ticket;
        }

        return $tickets_for_agent;
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

    #[Route('/dashboard/{id}/{data}', name: 'manager_changes', methods: ['post'])]
    public function manageTickets(TicketRepository $repository, Ticket $ticket, Request $request, Array $data): Response
    {
        var_dump($data);
        $ticket->setAssignedAgent($_POST['agent']);
        $ticket->setStatus(2);
        $ticket->setPriority($_POST['priority']);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('manager_index');
    }

}
