<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    public function load(ObjectManager $manager)
    {

        $customer = new User();
        $customer->setFirstName('Jan');
        $customer->setLastName('Pimpel');
        $customer->setEmail('jan@pimpel.com');
        $customer->setPassword($this->passwordEncoder
            ->encodePassword($customer, 'testpassword'));
        $customer->setRoles(['ROLE_USER']);
        $customer->setJoinedDate(new \DateTime('2021-05-11'));

        $boss = new User();
        $boss->setFirstName('Frank');
        $boss->setLastName('Bommel');
        $boss->setEmail('Frank@Bommel.com');
        $boss->setPassword($this->passwordEncoder
            ->encodePassword($customer, 'test2password'));
        $boss->setRoles(['ROLE_MANAGER']);
        $boss->setJoinedDate(new \DateTime('2021-05-11'));

        $agent = new User();
        $agent->setFirstName('Willy');
        $agent->setLastName('Huysmans');
        $agent->setEmail('Willy@Huysmans.com');
        $agent->setPassword($this->passwordEncoder
            ->encodePassword($customer, 'test2password'));
        $agent->setRoles(['ROLE_AGENT']);
        $agent->setJoinedDate(new \DateTime('2021-05-11'));

        $manager->persist($customer);
        $manager->persist($boss);
        $manager->persist($agent);



        $ticket = new Ticket();
        $ticket->setLevel(1);
        $ticket->setStatus(1);
        $ticket->setTicketOwner($customer);
        $ticket->setPriority(3);
        $ticket->setOpened(new \DateTime('2021-05-11'));
        $ticket->setSubject('Mailing issue');
        $ticket->setDescription('If I log in the mailing button does not work');

        $manager->persist($ticket);

        $comment = new Comment();
        $comment->setContent('We will come back to you anytime soon');
        $comment->setPrivate(1);
        $comment->setUserId($customer);
        $comment->setTicketId($ticket);


        $manager->persist($comment);

        $manager->flush();
    }
}
