<?php

namespace App\Controller\Visitor\Blog;

use App\Entity\Comment;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BlogController extends AbstractController
{
    #[Route('/liste-des-articles', name: 'app_visitor_blog_index', methods: ['GET'])]
    public function index(
        PostRepository $postRepository,
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        PaginatorInterface $paginator,
        Request $request,
    ): Response {
        $categorySlug = $request->query->get('category');
        $tagSlug = $request->query->get('tag');

        $queryBuilder = $postRepository->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.tags', 't')
            ->where('p.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('p.publishedAt', 'DESC');

        if ($categorySlug) {
            $queryBuilder
                ->andWhere('c.slug = :categorySlug')
                ->setParameter('categorySlug', $categorySlug);
        }

        if ($tagSlug) {
            $queryBuilder
                ->andWhere('t.slug = :tagSlug')
                ->setParameter('tagSlug', $tagSlug);
        }

        $posts = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('pages/visitor/blog/index.html.twig', [
            'posts' => $posts,
            'categories' => $categoryRepository->findBy([], ['name' => 'ASC']),
            'tags' => $tagRepository->findBy([], ['name' => 'ASC']),
            'currentCategory' => $categorySlug,
            'currentTag' => $tagSlug,
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

            if (!$user->isCanComment()) {
                $this->addFlash('danger', 'Vous n’êtes plus autorisé à publier des commentaires.');

                return $this->redirectToRoute('app_visitor_blog_show', [
                    'slug' => $post->getSlug(),
                ]);
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
