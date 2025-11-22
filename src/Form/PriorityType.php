<?php

namespace App\Form;

use App\Entity\Priority;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class PriorityType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title'),
                'attr' => ['class' => 'form-control', 'placeholder' => $this->translator->trans('Title')],
            ])
            ->add('color', TextType::class, [
                'label' => $this->translator->trans('Color'),
                'attr' => ['class' => 'form-control', 'placeholder' => $this->translator->trans('Color')],
            ])
            ->add('weight', IntegerType::class, [
                'label' => $this->translator->trans('Weight'),
                'attr' => ['class' => 'form-control', 'placeholder' => $this->translator->trans('Weight')],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Priority::class,
        ]);
    }
}
