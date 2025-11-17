<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\UserType;
use App\Service\Utils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/user')]
final class UserController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private Utils $utils, private TranslatorInterface $translator)
    {
    }

    #[Route('/', name: 'app_user', options: ['expose' => true])]
    public function index(): Response
    {
        // Fetch all users to display on the page
        $users = $this->em->getRepository(User::class)->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function new(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        // Create the User
        $user = new User();

        // Create the Form
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Process form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password
            $hashedPassword = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            // Save the User
            $this->em->persist($user);
            $this->em->flush();

            // Return a Turbo Stream response to update the user list
            return $this->utils->turboStreamResponse('app_user', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('user/user_new.turbo_stream.html.twig', [
            "form" => $form
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    // modal: edit user (returns modal fragment with prefilled form)
    #[Route('/user/{id}/modal-edit', name: 'app_user_edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function modalEdit(Request $request, User $user): Response
    {
        // Create the Form
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('user/user_edit.turbo_stream.html.twig', [
            "form" => $form
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    // modal: confirm delete user (returns modal fragment)
    #[Route('/delete/{id}', name: 'app_user_delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function modalDelete(Request $request, User $user): Response
    {
        if ($request->isMethod('POST')) {
            $this->em->remove($user);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('user.deleted', ['%email%' => $user->getEmail()]));
            // Return a Turbo Stream response to update the user list
            return $this->utils->turboStreamResponse('app_user', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('user/user_delete.turbo_stream.html.twig', [
            "user" => $user,
        ]);

        return $this->utils->turboStreamResponse($stream);
    }


}

