<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Ticket;
use Exception;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketManagerType extends AbstractType
{
    /**
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', HiddenType::class, ['data' => 1, 'attr' => ['value' => 1],])
            ->add('closed', HiddenType::class)
            ->add('priority', ChoiceType::class, ['choices' => [
                'P1'=>1,
                'P2'=>2,
                'P3'=>3,
                'P4'=>4,
                'P5'=>5,
            ]])
            ->add('level', ChoiceType::class, ['choices' => [
                'priority level 1' => 1,
                'priority level 2' => 2,
            ]])
            ->add('assignedAgent', EntityType::class, [
                'choice_label' => 'assigned agent',
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('closedBy', HiddenType::class)
            ->add('subject', HiddenType::class)
            ->add('description', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
