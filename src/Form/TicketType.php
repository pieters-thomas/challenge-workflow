<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Ticket;
use Exception;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    /**
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', HiddenType::class, ['data' => 1, 'attr' => ['value' => 1],])
            ->add('closed', HiddenType::class)
            ->add('priority', HiddenType::class, ['data' => 3])
            ->add('level', HiddenType::class, ['data' => 1])
            ->add('assignedAgent', HiddenType::class)
            ->add('closedBy', HiddenType::class)
            ->add('subject', TextType::class)
            ->add('description', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
