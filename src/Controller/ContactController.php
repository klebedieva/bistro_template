<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Form\ContactMessageType;
use App\Service\SymfonyEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SymfonyEmailService $emailService
    ) {}

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request): Response
    {
        $msg = new ContactMessage();
        $form = $this->createForm(ContactMessageType::class, $msg);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($msg);
            $this->em->flush();

            // Send notification to admin
            try {
                $this->emailService->sendNotificationToAdmin(
                    $msg->getEmail(),
                    $msg->getFirstName() . ' ' . $msg->getLastName(),
                    $msg->getSubject(),
                    $msg->getMessage()
                );
            } catch (\Exception $e) {
                // Log error but don't prevent saving
                error_log('Error sending notification to admin: ' . $e->getMessage());
            }

            $this->addFlash('success', 'Merci! Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('pages/contact.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }

    #[Route('/contact-ajax', name: 'app_contact_ajax', methods: ['POST'])]
    public function contactAjax(Request $request): JsonResponse
    {
        return $this->handleContactAjax($request);
    }
    
    private function handleContactAjax(Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        try {
            // Get form data from request
            $firstName = $request->request->get('firstName', '');
            $lastName = $request->request->get('lastName', '');
            $email = $request->request->get('email', '');
            $phone = $request->request->get('phone', '');
            $subject = $request->request->get('subject', '');
            $message = $request->request->get('message', '');
            $consent = $request->request->get('consent', false);
            
            // Validate form data
            $errors = [];
            
            if (empty($firstName) || strlen($firstName) < 2) {
                $errors[] = 'Le prénom est requis et doit contenir au moins 2 caractères';
            }
            
            if (empty($lastName) || strlen($lastName) < 2) {
                $errors[] = 'Le nom est requis et doit contenir au moins 2 caractères';
            }
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'L\'email est requis et doit être valide';
            }
            
            if (!empty($phone) && strlen($phone) < 10) {
                $errors[] = 'Le numéro de téléphone doit contenir au moins 10 caractères';
            }
            
            if (empty($subject)) {
                $errors[] = 'Le sujet est requis';
            }
            
            if (empty($message) || strlen($message) < 10) {
                $errors[] = 'Le message est requis et doit contenir au moins 10 caractères';
            }
            
            if (!$consent) {
                $errors[] = 'Vous devez accepter d\'être contacté';
            }
            
            if (!empty($errors)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Erreur de validation. Veuillez vérifier vos données.',
                    'errors' => $errors
                ], 400);
            }
            
            // Create and save contact message
            $contactMessage = new ContactMessage();
            $contactMessage->setFirstName($firstName);
            $contactMessage->setLastName($lastName);
            $contactMessage->setEmail($email);
            $contactMessage->setPhone($phone ?: null);
            $contactMessage->setSubject($subject);
            $contactMessage->setMessage($message);
            $contactMessage->setConsent($consent);
            
            $this->em->persist($contactMessage);
            $this->em->flush();

            // Send notification to admin
            try {
                $this->emailService->sendNotificationToAdmin(
                    $contactMessage->getEmail(),
                    $contactMessage->getFirstName() . ' ' . $contactMessage->getLastName(),
                    $contactMessage->getSubject(),
                    $contactMessage->getMessage()
                );
            } catch (\Exception $e) {
                error_log('Error sending notification to admin: ' . $e->getMessage());
            }
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Merci! Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.'
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi de votre message.'
            ], 500);
        }
    }
}