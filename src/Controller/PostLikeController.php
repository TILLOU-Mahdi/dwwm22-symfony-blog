<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Entity\User;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostLikeController extends AbstractController
{
    #[Route('/post/{id}/like', name: 'app_post_like', methods: ['GET'])]
    public function toggle(
        Post $post,
        PostLikeRepository $postLikeRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifie si déjà liké
        $existingLike = $postLikeRepository->findOneBy([
            'post' => $post,
            'user' => $user,
        ]);

        if ($existingLike) {
            // UNLIKE
            $entityManager->remove($existingLike);
        } else {
            // LIKE
            $like = new PostLike();
            $like->setPost($post);
            $like->setUser($user);
            $like->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($like);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_visitor_blog_show', [
            'slug' => $post->getSlug(),
        ]);
    }
}
