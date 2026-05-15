<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_user_profile', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $profileForm = $this->createForm(UserProfileType::class, $user);
        $profileForm->handleRequest($request);

        $passwordForm = $this->createForm(ChangePasswordFormType::class);
        $passwordForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre profil a bien été mis à jour.'
            );

            return $this->redirectToRoute('app_user_profile');
        }

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $currentPassword = $passwordForm->get('currentPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $passwordForm->get('currentPassword')->addError(
                    new FormError('Le mot de passe actuel est incorrect.')
                );
            } else {
                $newPassword = $passwordForm->get('plainPassword')->getData();

                $user->setPassword(
                    $passwordHasher->hashPassword($user, $newPassword)
                );

                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'Votre mot de passe a bien été modifié.'
                );

                return $this->redirectToRoute('app_user_profile');
            }
        }

        return $this->render('pages/user/profile/index.html.twig', [
            'profileForm' => $profileForm->createView(),
            'passwordForm' => $passwordForm->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/profile/delete', name: 'app_user_profile_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        UserPasswordHasherInterface $passwordHasher,
    ): RedirectResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (
            !$this->isCsrfTokenValid(
                'delete_profile_'.$user->getId(),
                $request->request->get('_token')
            )
        ) {
            $this->addFlash(
                'danger',
                'La suppression du compte a échoué.'
            );

            return $this->redirectToRoute('app_user_profile');
        }

        $deleteAccountPassword = $request->request->get('delete_account_password');

        if (
            !$passwordHasher->isPasswordValid(
                $user,
                $deleteAccountPassword
            )
        ) {
            $this->addFlash(
                'danger',
                'Le mot de passe saisi est incorrect.'
            );

            return $this->redirectToRoute('app_user_profile');
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

        foreach ($user->getResetPasswordRequests() as $resetPasswordRequest) {
            $entityManager->remove($resetPasswordRequest);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $security->logout(false);

        $this->addFlash(
            'success',
            'Votre compte a bien été supprimé.'
        );

        return $this->redirectToRoute('app_visitor_welcome');
    }
}
