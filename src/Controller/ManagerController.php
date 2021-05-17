<?php

namespace App\Controller;


use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ManagerController extends AbstractController
{
    //#[Route('/manager', name: 'manager')]
    //public function index(): Response

    /**
     * @param TicketRepository $ticketRepository
     * @param UserInterface $user
     * @return Response
     */
    #[Route('/manager', name: 'manager_index', methods: ['GET'])]
    public function showAllOpenTickets(UserRepository $userRepository, TicketRepository $ticketRepository, UserInterface $user): Response
    {
        $role = $user->getRoles()[0];

        // This page is only available for Managers
        if($role != "ROLE_MANAGER")
        {
            return $this->redirectToRoute('app_login');
        }

        $open_tickets = 0;
        $closed_tickets = 0;

        // find the agents reporting to this manager
        $agents = $this->getAgentsForManager($userRepository, $user);
        $all_agents = $this->getAllAgents($userRepository);

        // Get an overview of all tickets assigned to agents...
        $ticketOverview = array();
        foreach($agents as $agent){
            // for each agent... get the tickets
            $tickets_for_agent = $this->getTicketsForAgent($ticketRepository, $agent);

            foreach($tickets_for_agent as $ticket) {

                switch($ticket->getStatus())
                {
                    // See Ticket.php, so we don't have to remember what number is what state...
                    case Ticket::OPEN:
                        $open_tickets++;
                        $ticket_status = "Open";
                        break;

                    case Ticket::CLOSED:
                        $closed_tickets++;
                        $ticket_status = "Closed";
                        break;

                    case Ticket::WONTFIX:
                        $ticket_status = "Won't Fix";
                        break;

                    default:
                        $ticket_status = "New";
                        break;
                }

                // for each ticket... add the relevant properties to the agent in question
                // eg: $ticketOverview[Jan] = [
                //   'id'=> '12345',
                //   'subject' => 'crash happened',
                //   'priority' => 3,
                //   'status' => "Open",
                //   'owner' => 1
                // ]

                $ticketOverview[$agent->getFirstName()] = [
                    'id' => $ticket->getId(),
                    'subject' => $ticket->getSubject(),
                    'priority' => $ticket->getPriority(),
                    'status' => $ticket_status,
                    'owner' => $ticket->getTicketOwner(),
                ];

            }

        }
        // Get all the tickets that are not yet assigned (assignedAgent = null) as objects
        $unassigned_ticket_objects = $ticketRepository->findBy(['assignedAgent'=> null],['priority'=>'ASC']);

        // Create an array with only the fields we want for the view
        $unassigned_tickets = array();
        foreach ($unassigned_ticket_objects as $ticket)
        {
            switch($ticket->getStatus())
            {

                case Ticket::OPEN:
                    $ticket_status = "Open"; break;
                case Ticket::CLOSED:
                    $ticket_status = "Closed"; break;
                case Ticket::WONTFIX:
                    $ticket_status = "Won't Fix"; break;
                default:
                    $ticket_status = "New"; break;
            }

            $unassigned_tickets[] = [
                'id' => $ticket->getId(),
                'subject' => $ticket->getSubject(),
                'priority' => $ticket->getPriority(),
                'status' => $ticket_status,
                'owner' => $ticket->getTicketOwner(),
            ];

        }

        // Possible statuses for the status dropdown
        $available_statuses = [];
        $available_statuses[] = ['id' => Ticket::OPEN, 'name' => "Open"];
        $available_statuses[] = ['id' => Ticket::CLOSED, 'name' => "Closed"];

        // Possible agents for the agent dropdown
        $available_agents = [];
        foreach($all_agents as $agent) {
            $available_agents[] = ['id' => $agent->getId(), 'name' => $agent->getFirstName()];
        }


        return $this->render('manager/index.html.twig', [
            'ticketOverview' => $ticketOverview,
            'closedTickets' => $closed_tickets,
            'openTickets' => $open_tickets,
            'availableAgents' => $available_agents,
            'unassignedTickets' => $unassigned_tickets,
            'availableStatuses' => $available_statuses,
            'loggedInManager' => $user->getFirstName(),
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

    public function getAgentsForManager(UserRepository $userRepository, UserInterface $user): array
    {
        /** @var User $user */
        $managerId = $user->getId();
        return $userRepository->findBy(['agents' => $managerId],['firstName'=>'ASC']);
    }

    public function getAllAgents(UserRepository $userRepository): array
    {
        // There must be a better way to do this....
        // Find all users, and if the string "ROLE_AGENT" is in the 'roles' column, we assume it's an agent
        //
        $agents = [];
        $all_users = $userRepository->findAll();

        foreach ($all_users as $user)
        {
            if(in_array("ROLE_AGENT", $user->getRoles()))
                $agents[] = $user;
        }
        return $agents; //$userRepository->findBy(['roles' => 'ROLE_AGENT'],['firstName'=>'ASC']);
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
