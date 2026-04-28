<?php

namespace App\Controller\Visitor\Blog;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/liste-des-articles/{slug}', name: 'app_visitor_blog_show', methods: ['GET'])]
    public function show(string $slug, PostRepository $postRepository): Response
    {
        $post = $postRepository->findOneBy([
            'slug' => $slug,
            'isPublished' => true,
        ]);

        if (!$post) {
            throw $this->createNotFoundException();
        }

        return $this->render('pages/visitor/blog/show.html.twig', [
            'post' => $post,
        ]);
    }
}