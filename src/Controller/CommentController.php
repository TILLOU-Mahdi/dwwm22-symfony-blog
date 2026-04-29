<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentController extends AbstractController
{
    #[Route('/commentaire/{id}/modifier', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($comment->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_visitor_blog_show', [
                'slug' => $comment->getPost()->getSlug(),
            ]);
        }

        return $this->render('comment/edit.html.twig', [
            'commentForm' => $form->createView(),
            'comment' => $comment,
        ]);
    }

    #[Route('/commentaire/{id}/supprimer', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Comment $comment, EntityManagerInterface $entityManager): Response
    {
        // ✅ auteur OU admin
        if ($comment->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $slug = $comment->getPost()->getSlug();

        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('app_visitor_blog_show', [
            'slug' => $slug,
        ]);
    }
}
