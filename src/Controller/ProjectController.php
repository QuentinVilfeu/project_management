<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Service\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/project')]
final class ProjectController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private Utils $utils, private TranslatorInterface $translator)
    {
    }

    #[Route('/', name: 'app_project', options: ['expose' => true])]
    public function index(): Response
    {
        $projects = $this->em->getRepository(Project::class)->findAll();

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/new', name: 'app_project_new', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function new(Request $request): Response
    {
        // Create the Project
        $project = new Project();

        // Create the Form
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        // Process form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Save the Project
            $this->em->persist($project);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('project.added', ['%title%' => $project->getTitle()]));
            // Return a Turbo Stream response to update the project list
            return $this->utils->turboStreamResponse('app_project', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('project/project_new.turbo_stream.html.twig', [
            "form" => $form
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    // modal: edit project (returns modal fragment with prefilled form)
    #[Route('/edit/{id}', name: 'app_project_edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(Request $request, Project $project): Response
    {
        // Create the Form
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        // Process form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Save the Project
            $this->em->persist($project);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('project.edited', ['%title%' => $project->getTitle()]));
            // Return a Turbo Stream response to update the project list
            return $this->utils->turboStreamResponse('app_project', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('project/project_edit.turbo_stream.html.twig', [
            "project" => $project,
            "form" => $form
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    // modal: confirm delete project (returns modal fragment)
    #[Route('/delete/{id}', name: 'app_project_delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function delete(Request $request, Project $project): Response
    {
        if ($request->isMethod('POST')) {
            $this->em->remove($project);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('project.deleted', ['%title%' => $project->getTitle()]));
            // Return a Turbo Stream response to update the project list
            return $this->utils->turboStreamResponse('app_project', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('project/project_delete.turbo_stream.html.twig', [
            "project" => $project,
        ]);

        return $this->utils->turboStreamResponse($stream);
    }
}
