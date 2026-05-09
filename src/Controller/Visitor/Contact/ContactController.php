<?php

namespace App\Controller\Visitor\Contact;

use App\Entity\ContactMessage;
use App\Entity\User;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/me-contacter', name: 'app_visitor_contact_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $contactMessage = new ContactMessage();
        $contactMessage->setEmail($user->getEmail());

        $form = $this->createForm(ContactType::class, $contactMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactMessage->setEmail($user->getEmail());
            $contactMessage->setUser($user);
            $contactMessage->setCreatedAt(new \DateTimeImmutable());
            $contactMessage->setIsRead(false);

            $entityManager->persist($contactMessage);
            $entityManager->flush();

            $this->addFlash('success', 'Votre message a bien été envoyé.');

            return $this->redirectToRoute('app_visitor_contact_index');
        }

        return $this->render('pages/visitor/contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}
