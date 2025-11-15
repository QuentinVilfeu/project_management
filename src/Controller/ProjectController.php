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

#[Route('/project')]
final class ProjectController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private Utils $utils)
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

            // Return a Turbo Stream response to update the project list
            return $this->utils->turboStreamResponse('app_project', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('project/project_new.turbo_stream.html.twig', [
            "form" => $form
        ]);

        return $this->utils->turboStreamResponse($stream);
    }
}
