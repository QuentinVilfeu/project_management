<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Service\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/task')]
final class TaskController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Utils $utils,
        private TranslatorInterface $translator
    )
    {
    }

    #[Route('/', name: 'app_task', options: ['expose' => true])]
    public function index(): Response
    {
        $tasks = $this->em->getRepository(Task::class)->findAll();

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function new(Request $request): Response
    {
        // Create the Task
        $task = new Task();

        // Create the Form
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        // Process form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Save the Task
            $this->em->persist($task);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('task.added', ['%title%' => $task->getTitle()]));
            // Return a Turbo Stream response to update the task list
            return $this->utils->turboStreamResponse('app_task', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('task/task_new.turbo_stream.html.twig', [
            "form" => $form
        ]);

        return $this->utils->turboStreamResponse($stream);
    }


    #[Route('/edit/{id}', name: 'app_task_edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(Request $request, Task $task): Response
    {
        // Create the Form
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        // Process form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Save the Task
            $this->em->persist($task);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('task.edited', ['%title%' => $task->getTitle()]));
            // Return a Turbo Stream response to update the task list
            return $this->utils->turboStreamResponse('app_task', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('task/task_edit.turbo_stream.html.twig', [
            "task" => $task,
            "form" => $form
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    #[Route('/delete/{id}', name: 'app_task_delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function delete(Request $request, Task $task): Response
    {
        if ($request->isMethod('POST')) {
            $this->em->remove($task);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('task.deleted', ['%title%' => $task->getTitle()]));
            // Return a Turbo Stream response to update the task list
            return $this->utils->turboStreamResponse('app_task', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('task/task_delete.turbo_stream.html.twig', [
            "task" => $task,
        ]);

        return $this->utils->turboStreamResponse($stream);
    }
}
