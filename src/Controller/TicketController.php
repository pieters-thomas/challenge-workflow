<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\Ticket;
use App\Form\CommentType;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;


#[Route('/ticket')]
class TicketController extends AbstractController
{

    #[Route('/', name: 'ticket_index', methods: ['GET'])]
    #[IsGranted (['ROLE_USER'])]
    public function showAllOpenTickets(TicketRepository $ticketRepository, UserInterface $user): ?Response
    {
        /**
         * @var Ticket $ticket
         * @var User $user
         *
         */
        $role = $user->getRoles()[0];
        return $this->render('ticket/index.html.twig', [
            'tickets' => $ticketRepository->findBy(['ticketOwner' => $this->getUser()]),
        ]);
    }

    #[Route('/new', name: 'ticket_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $ticket->setOpened(date_create(date("Y-m-d H:i:s")));
            $ticket->setTicketOwner($user);
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('ticket_index');
        }

        return $this->render('ticket/new.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'ticket_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Ticket $ticket): Response
    {
        /**
         * @var User $user
         * @var Ticket $ticket
         */
        $user = $this->getUser();
        $comment = new Comment();
        $comment->setTicketId($ticket);
        $comment->setUserId($user);

        $comments = $ticket->getComments();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setContent($user->getFirstName() . ' ' . $comment->getContent());
            $this->getDoctrine()->getManager()->persist($comment);
            $this->getDoctrine()->getManager()->flush();

            $this->redirectToRoute('ticket_show', ['id' => $ticket->getId()]);
        }
        return $this->render('ticket/show.html.twig', [
            'user' => $user,
            'ticket' => $ticket,
            'comments' => $comments,
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

    #[Route('/{id}', name: 'ticket_delete', methods: ['POST'])]
    public function delete(Request $request, Ticket $ticket): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ticket->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ticket_index');
    }
}
