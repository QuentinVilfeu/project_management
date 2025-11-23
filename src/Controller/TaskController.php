<?php

namespace App\Controller;

use App\Entity\Priority;
use App\Entity\State;
use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskCloseType;
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

    #[Route('/page/{id}', name: 'app_task_page', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function page(Request $request, Task $task): Response
    {
        $states = $this->em->getRepository(State::class)->findAll();
        $priorities = $this->em->getRepository(Priority::class)->findAll();
        $users = $this->em->getRepository(User::class)->findAll();

        return $this->render('task/task_page.html.twig', [
            'task' => $task,
            'states' => $states,
            'priorities' => $priorities,
            'users' => $users,
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
        $stream = $this->renderView('task/task.turbo_stream.html.twig', [
            "action" => "actionNew",
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
        $stream = $this->renderView('task/task.turbo_stream.html.twig', [
            "action" => "actionEdit",
            "task" => $task,
            "form" => $form
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    #[Route('/close/{id}', name: 'app_task_close', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function close(Request $request, Task $task): Response
    {
        // Create the Form
        $form = $this->createForm(TaskCloseType::class, $task);
        $form->handleRequest($request);

        // Process form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Save the Task
            $this->em->persist($task);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('task.closed', ['%title%' => $task->getTitle()]));
            // Return a Turbo Stream response to update the task list
            return $this->utils->turboStreamResponse('app_task', true);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('task/task.turbo_stream.html.twig', [
            "action" => "actionClose",
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
        $stream = $this->renderView('task/task.turbo_stream.html.twig', [
            "action" => "actionDelete",
            "task" => $task,
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    #[Route('/action/edit/priority/{id}/{priorityId}', name: 'app_task_action_edit_priority', methods: ['POST'], options: ['expose' => true])]
    public function actioneditPriority(Task $task, int $priorityId): Response
    {
        $priority = $this->em->getRepository(Priority::class)->find($priorityId);
        $priorities = $this->em->getRepository(Priority::class)->findAll();

        $task->setPriority($priority);
        $this->em->persist($task);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('task.edited', ['%title%' => $task->getTitle()]));

        $stream = $this->renderView('task/task.turbo_stream.html.twig', [
            "action" => 'actionEditPriority',
            "task" => $task,
            "priorities" => $priorities,
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    #[Route('/action/edit/state/{id}/{stateId}', name: 'app_task_action_edit_state', methods: ['POST'], options: ['expose' => true])]
    public function actioneditState(Task $task, int $stateId): Response
    {
        $state = $this->em->getRepository(State::class)->find($stateId);
        $states = $this->em->getRepository(State::class)->findAll();

        $task->setState($state);
        $this->em->persist($task);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('task.edited', ['%title%' => $task->getTitle()]));

        $stream = $this->renderView('task/task.turbo_stream.html.twig', [
            "action" => 'actionEditState',
            "task" => $task,
            "states" => $states,
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    #[Route('/action/edit/assignee/{id}/{assigneeId}', name: 'app_task_action_edit_assignee', methods: ['POST'], options: ['expose' => true])]
    public function actioneditAssignee(Task $task, int $assigneeId): Response
    {
        $user = $this->em->getRepository(User::class)->find($assigneeId);
        $users = $this->em->getRepository(User::class)->findAll();

        $task->setAssignee($user);
        $this->em->persist($task);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('task.edited', ['%title%' => $task->getTitle()]));

        $stream = $this->renderView('task/task.turbo_stream.html.twig', [
            "action" => 'actionEditAssignee',
            "task" => $task,
            "users" => $users,
        ]);

        return $this->utils->turboStreamResponse($stream);
    }


}
