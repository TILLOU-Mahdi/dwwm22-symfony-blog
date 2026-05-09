<?php

namespace App\Controller\User\Home;

use App\Entity\ContactMessage;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\ContactMessageRepository;
use App\Repository\PostLikeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_user_home', methods: ['GET'])]
    public function index(
        ContactMessageRepository $contactMessageRepository,
        CommentRepository $commentRepository,
        PostLikeRepository $postLikeRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $contactMessages = $contactMessageRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        $comments = $commentRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        $postLikes = $postLikeRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('pages/user/home/index.html.twig', [
            'contactMessages' => $contactMessages,
            'comments' => $comments,
            'postLikes' => $postLikes,
        ]);
    }

    #[Route('/message/{id}', name: 'app_user_contact_message_show', methods: ['GET'])]
    public function show(ContactMessage $contactMessage): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User || $contactMessage->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('pages/user/home/show_message.html.twig', [
            'message' => $contactMessage,
        ]);
    }
}
