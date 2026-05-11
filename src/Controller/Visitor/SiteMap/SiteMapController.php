<?php

namespace App\Controller\Visitor\SiteMap;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SiteMapController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
    ) {
    }

    #[Route('/sitemap.xml', name: 'app_visitor_sitemap_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $hostName = $request->getSchemeAndHttpHost();

        $urls = [];
        $urls[] = [
            'loc' => $this->generateUrl('app_visitor_welcome'),
        ];

        $posts = $this->postRepository->findBy(['isPublished' => true], ['publishedAt' => 'DESC']);

        foreach ($posts as $post) {
            $urls[] = [
                'loc' => $this->generateUrl('app_visitor_blog_show', [
                    'slug' => $post->getSlug(),
                ]),
                'lastmod' => $post->getUpdatedAt()->format('Y-m-d'),
                'priority' => 0.9,
                'changefreq' => 'weekly',
            ];
        }

        $response = $this->render('pages/visitor/site_map/index.html.twig', [
            'host_name' => $hostName,
            'urls' => $urls,
        ]);

        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
