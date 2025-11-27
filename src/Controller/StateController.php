<?php

namespace App\Controller;

use App\Entity\State;
use App\Form\StateType;
use App\Service\StateService;
use App\Service\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/state')]
final class StateController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Utils $utils,
        private TranslatorInterface $translator,
        private StateService $stateService
    )
    {
    }

    #[Route('/', name: 'app_state', options: ['expose' => true])]
    public function index(): Response
    {
        // Fetch all states to display on the page
        $states = $this->em->getRepository(State::class)->findBy([], ['weight' => 'ASC']);

        return $this->render('state/index.html.twig', [
            'states' => $states,
        ]);
    }

    #[Route('/new', name: 'app_state_new', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function new(Request $request): Response
    {
        // Create the State
        $state = new State();

        // Create the Form
        $form = $this->createForm(StateType::class, $state);
        $form->handleRequest($request);

        // Process form submission
        if ($form->isSubmitted() && $form->isValid()) {
            $this->stateService->removeAllClosingStateBool();
            // Save the State
            $this->em->persist($state);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('state.added', ['%title%' => $state->getTitle()]));
            // Return a Turbo Stream response to update the state list
            return $this->utils->turboStreamResponse('app_state', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('state/state_new.turbo_stream.html.twig', [
            "form" => $form
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    // modal: edit state (returns modal fragment with prefilled form)
    #[Route('/edit/{id}', name: 'app_state_edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(Request $request, State $state): Response
    {
        // Create the Form
        $form = $this->createForm(StateType::class, $state);
        $form->handleRequest($request);

        // Process form submission
        if ($form->isSubmitted() && $form->isValid()) {
            $this->stateService->removeAllClosingStateBool($state);
            // Save the State
            $this->em->persist($state);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('state.edited', ['%title%' => $state->getTitle()]));
            // Return a Turbo Stream response to update the state list
            return $this->utils->turboStreamResponse('app_state', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('state/state_edit.turbo_stream.html.twig', [
            "state" => $state,
            "form" => $form
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    // modal: confirm delete state (returns modal fragment)
    #[Route('/delete/{id}', name: 'app_state_delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function delete(Request $request, State $state): Response
    {
        if ($request->isMethod('POST')) {
            $this->em->remove($state);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('state.deleted', ['%title%' => $state->getTitle()]));
            // Return a Turbo Stream response to update the state list
            return $this->utils->turboStreamResponse('app_state', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('state/state_delete.turbo_stream.html.twig', [
            "state" => $state,
        ]);

        return $this->utils->turboStreamResponse($stream);
    }
}
