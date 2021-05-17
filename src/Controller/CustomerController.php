<?php


namespace App\Controller;


use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\TicketRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * @IsGranted("ROLE_USER")
 */
#[Route('/customer')]
class CustomerController extends AbstractController
{
    
    #[Route('/customer', name: 'customer_index', methods: ['GET'])]
    public function index(TicketRepository $ticketRepository, UserInterface $user): Response
    {
        /** @var User $user */
        $id = $user->getId();
        return $this->render('customer/index.html.twig', [
            'tickets' => $ticketRepository->findBy(['ticketOwner' => $id], ['opened' => 'DESC']),
        ]);
    }

    #[Route('/{id}', name: 'ticket_details', methods: ['GET', 'POST'])]
    public function show(Request $request, Ticket $ticket): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (($user !== $ticket->getTicketOwner()) && $user->getRoles() !== ['ROLE_AGENT']) {
            return $this->redirectToRoute('app_login');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUserId($user);
            $comment->setTicketId($ticket);
            $ticket->setStatus(2);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('ticket_details', ['id' => $ticket->getId()]);
        }
        return $this->render('customer/show.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView()]);
    }


}