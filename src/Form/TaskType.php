<?php

namespace App\Form;

use App\Entity\Priority;
use App\Entity\Project;
use App\Entity\State;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title')
            ])
            ->add('description', TextareaType::class, [
                'label' => $this->translator->trans('Description')
            ])
            ->add('initialEndDate', DateType::class, [
                'required' => false,
                'label' => $this->translator->trans('Initial end date')
            ])
            ->add('closingDate', DateType::class, [
                'required' => false,
                'label' => $this->translator->trans('Closing date')
            ])
            ->add('project', EntityType::class, [
                'label' => $this->translator->trans('Project'),
                'class' => Project::class,
                'choice_label' => 'title',
                'placeholder' => $this->translator->trans('-- Select a project --')
            ])
            ->add('state', EntityType::class, [
                'label' => $this->translator->trans('State'),
                'class' => State::class,
                'choice_label' => 'title',
            ])
            ->add('priority', EntityType::class, [
                'label' => $this->translator->trans('Priority'),
                'class' => Priority::class,
                'choice_label' => 'title',
            ])
            ->add('assignee', EntityType::class, [
                'required' => false,
                'label' => $this->translator->trans('Assignee'),
                'class' => User::class,
                'choice_label' => 'email',
                'placeholder' => $this->translator->trans('-- Select a user --')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
