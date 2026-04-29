<?php

namespace App\Controller\Visitor\Contact;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/me-contacter', name: 'app_visitor_contact_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Votre message a bien été envoyé.');

            return $this->redirectToRoute('app_visitor_contact_index');
        }

        return $this->render('pages/visitor/contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}
