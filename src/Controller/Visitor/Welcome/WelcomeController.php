<?php

namespace App\Controller\Visitor\Welcome;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WelcomeController extends AbstractController
{
    #[Route('/', name: 'app_visitor_welcome', methods: ['GET'])]
    public function index(
        PostRepository $postRepository,
        CategoryRepository $categoryRepository,
    ): Response {
        $latestPosts = $postRepository->findBy([], ['createdAt' => 'DESC'], 3);
        $categories = $categoryRepository->findBy([], ['name' => 'ASC'], 6);

        return $this->render('pages/visitor/welcome/index.html.twig', [
            'latestPosts' => $latestPosts,
            'categories' => $categories,
        ]);
    }
}
