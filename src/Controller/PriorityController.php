<?php

namespace App\Controller;

use App\Entity\Priority;
use App\Form\PriorityType;
use App\Service\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/priority')]
final class PriorityController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private Utils $utils, private TranslatorInterface $translator)
    {
    }

    #[Route('/', name: 'app_priority', options: ['expose' => true])]
    public function index(): Response
    {
        $priorities = $this->em->getRepository(Priority::class)->findAll();

        return $this->render('priority/index.html.twig', [
            'priorities' => $priorities,
        ]);
    }

    #[Route('/new', name: 'app_priority_new', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function new(Request $request): Response
    {
        // Create the Priority
        $priority = new Priority();

        // Create the Form
        $form = $this->createForm(PriorityType::class, $priority);
        $form->handleRequest($request);

        // Process form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Save the Priority
            $this->em->persist($priority);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('priority.added', ['%title%' => $priority->getTitle()]));
            // Return a Turbo Stream response to update the priority list
            return $this->utils->turboStreamResponse('app_priority', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('priority/priority_new.turbo_stream.html.twig', [
            "form" => $form
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    #[Route('/edit/{id}', name: 'app_priority_edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(Request $request, Priority $priority): Response
    {
        // Create the Form
        $form = $this->createForm(PriorityType::class, $priority);
        $form->handleRequest($request);

        // Process form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Save the Priority
            $this->em->persist($priority);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('priority.edited', ['%title%' => $priority->getTitle()]));
            // Return a Turbo Stream response to update the priority list
            return $this->utils->turboStreamResponse('app_priority', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('priority/priority_edit.turbo_stream.html.twig', [
            "priority" => $priority,
            "form" => $form
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    #[Route('/delete/{id}', name: 'app_priority_delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function delete(Request $request, Priority $priority): Response
    {
        if ($request->isMethod('POST')) {
            $this->em->remove($priority);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('priority.deleted', ['%title%' => $priority->getTitle()]));
            // Return a Turbo Stream response to update the priority list
            return $this->utils->turboStreamResponse('app_priority', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('priority/priority_delete.turbo_stream.html.twig', [
            "priority" => $priority,
        ]);

        return $this->utils->turboStreamResponse($stream);
    }
}
