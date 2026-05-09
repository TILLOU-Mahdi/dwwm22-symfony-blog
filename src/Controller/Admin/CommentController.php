<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/comments')]
final class CommentController extends AbstractController
{
    #[Route('', name: 'app_admin_comment_index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('pages/admin/comment/index.html.twig', [
            'comments' => $commentRepository->findBy([], [
                'createdAt' => 'DESC',
            ]),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_comment_delete', methods: ['POST'])]
    public function delete(Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Le commentaire a bien été supprimé.');

        return $this->redirectToRoute('app_admin_comment_index');
    }
}
