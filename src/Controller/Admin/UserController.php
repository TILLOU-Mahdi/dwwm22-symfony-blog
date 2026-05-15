<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/users')]
final class UserController extends AbstractController
{
    #[Route('', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('pages/admin/user/index.html.twig', [
            'users' => $userRepository->findBy([], [
                'createdAt' => 'DESC',
            ]),
        ]);
    }

    #[Route('/{id}/toggle-comment', name: 'app_admin_user_toggle_comment', methods: ['POST'])]
    public function toggleComment(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setCanComment(!$user->isCanComment());

        $entityManager->flush();

        $this->addFlash('success', 'Le droit de commenter de l’utilisateur a été mis à jour.');

        return $this->redirectToRoute('app_admin_user_index');
    }

    #[Route('/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$this->isCsrfTokenValid('delete_user_'.$user->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Suppression impossible.');

            return $this->redirectToRoute('app_admin_user_index');
        }

        foreach ($user->getComments() as $comment) {
            $comment->setUser(null);
        }

        foreach ($user->getLikes() as $like) {
            $entityManager->remove($like);
        }

        foreach ($user->getContactMessages() as $contactMessage) {
            $contactMessage->setUser(null);
        }

        foreach ($user->getPosts() as $post) {
            $post->setUser(null);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Le compte utilisateur a bien été supprimé.');

        return $this->redirectToRoute('app_admin_user_index');
    }
}
