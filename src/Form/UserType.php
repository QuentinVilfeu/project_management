<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('Email'),
                'attr' => ['class' => 'form-control', 'placeholder' => $this->translator->trans('Email')],
            ])
            ->add('password', PasswordType::class, [
                'label' => $this->translator->trans('Password'),
                'attr' => ['class' => 'form-control', 'placeholder' => $this->translator->trans('Password')],
            ])
            ->add('firstname', TextType::class, [
                'label' => $this->translator->trans('Firstname'),
                'attr' => ['class' => 'form-control', 'placeholder' => $this->translator->trans('Firstname')],
            ])
            ->add('lastname', TextType::class, [
                'label' => $this->translator->trans('Lastname'),
                'attr' => ['class' => 'form-control', 'placeholder' => $this->translator->trans('Lastname')],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
