<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Priority;
use App\Entity\State;
use App\Entity\Task;
use App\Entity\User;
use App\Form\CommentType;
use App\Service\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/comment')]
final class CommentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Utils $utils,
        private TranslatorInterface $translator
    )
    {
    }

    #[Route('/', name: 'app_comment', options: ['expose' => true])]
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    #[Route('/{id}', name: 'app_comment_edit', methods: ['GET', 'POST'], options: ['expose' => true], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Comment $comment): Response
    {
        // Create the Form
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        // Process form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Save the Comment
            $this->em->persist($comment);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('comment.edited'));

            $comments   = $this->em->getRepository(Task::class)->getFullComments($comment->getTask());

            $this->addFlash('success', $this->translator->trans('task.commented', ['%title%' => $comment->getTask()->getTitle()]));
            $stream = $this->renderView('task/task.turbo_stream.html.twig', [
                "action" => 'actionCommentSection',
                "comments" => $comments,
                "task" => $comment->getTask()
            ]);

            return $this->utils->turboStreamResponse($stream);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('comment/comment.turbo_stream.html.twig', [
            "action" => "actionEdit",
            "comment" => $comment,
            "form" => $form
        ]);

        return $this->utils->turboStreamResponse($stream);
    }

    #[Route('/{id}/delete', name: 'app_comment_delete', methods: ['GET', 'POST'], options: ['expose' => true], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Comment $comment): Response
    {
        if ($request->isMethod('POST')) {
            $task = $comment->getTask();
            $this->em->remove($comment);
            $this->em->flush();

            $comments   = $this->em->getRepository(Task::class)->getFullComments($task);

            $this->addFlash('success', $this->translator->trans('comment.deleted'));
            $stream = $this->renderView('task/task.turbo_stream.html.twig', [
                "action" => 'actionCommentSection',
                "comments" => $comments,
                "task" => $task
            ]);

            return $this->utils->turboStreamResponse($stream);
        }

        // Render the form in a Turbo Stream response
        $stream = $this->renderView('comment/comment.turbo_stream.html.twig', [
            "action" => "actionDelete",
            "comment" => $comment,
        ]);

        return $this->utils->turboStreamResponse($stream);
    }
}
