<?php

namespace App\Form;

use App\Entity\User;
use App\Service\RoleHierarchyService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserEditType extends AbstractType
{
    public function __construct(
        private TranslatorInterface $translator,
        private Security $security,
        private RoleHierarchyService $roleHierarchyService,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $allowedRoles = $this->roleHierarchyService->getAllowedRoles();

        $builder
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('Email'),
                'attr' => ['class' => 'form-control', 'placeholder' => $this->translator->trans('Email')],
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

        if (! $this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $builder
            ->add('roles', ChoiceType::class, [
                'choices' => $allowedRoles,
                'multiple' => true,
                'expanded' => true,
                'label' => $this->translator->trans('Roles to be assigned'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'edit_user_form'; 
    }
}
