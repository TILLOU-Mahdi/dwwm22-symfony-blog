<?php

namespace App\Controller\Visitor\Blog;

use App\Entity\Comment;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BlogController extends AbstractController
{
    #[Route('/liste-des-articles', name: 'app_visitor_blog_index', methods: ['GET'])]
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $postRepository->createQueryBuilder('p')
            ->where('p.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();

        $posts = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('pages/visitor/blog/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/liste-des-articles/{slug}', name: 'app_visitor_blog_show', methods: ['GET', 'POST'])]
    public function show(
        string $slug,
        PostRepository $postRepository,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $post = $postRepository->findOneBy([
            'slug' => $slug,
            'isPublished' => true,
        ]);

        if (!$post) {
            throw $this->createNotFoundException();
        }

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if (!$user instanceof User) {
                return $this->redirectToRoute('app_login');
            }

            $comment->setPost($post);
            $comment->setUser($user);
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setIsApproved(true);

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_visitor_blog_show', [
                'slug' => $post->getSlug(),
            ]);
        }

        return $this->render('pages/visitor/blog/show.html.twig', [
            'post' => $post,
            'commentForm' => $form->createView(),
        ]);
    }
}