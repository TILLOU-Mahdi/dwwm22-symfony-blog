<?php

namespace App\Controller\Visitor\About;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    #[Route('/a-propos-de-paul', name: 'app_visitor_about_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/visitor/about/index.html.twig');
    }
}
