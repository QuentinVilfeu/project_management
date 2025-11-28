<?php

namespace App\Form;

use App\Entity\Priority;
use App\Entity\Project;
use App\Entity\State;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskReopenType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('state', EntityType::class, [
                'label' => $this->translator->trans('State'),
                'class' => State::class,
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('s')
                        ->andWhere('s.isClosingState = false')
                        ->orderBy('s.weight', 'ASC');
                },
            ])
            ->add('priority', EntityType::class, [
                'label' => $this->translator->trans('Priority'),
                'class' => Priority::class,
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.weight', 'ASC');
                },
            ])
            ->add('newComment', CommentType::class, [
                'label' => false,
                'mapped' => false,
                'required' => true,
                'translation_domain' => 'messages',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'validation_groups' => ['reopen'],
        ]);
    }
}
