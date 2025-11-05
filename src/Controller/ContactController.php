<?php

namespace App\Controller;

use App\DTO\ContactCreateRequest;
use App\Entity\ContactMessage;
use App\Form\ContactMessageType;
use App\Service\InputSanitizer;
use App\Service\SymfonyEmailService;
use App\Service\ValidationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Contact Form Controller
 * 
 * Handles contact form submissions:
 * - Display contact form (GET)
 * - Process form submission (POST)
 * - AJAX form submission endpoint
 * 
 * All user input is sanitized to prevent XSS attacks before saving to database.
 * Email notifications are sent to admin (non-blocking - failures are logged).
 */
class ContactController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SymfonyEmailService $emailService,
        private LoggerInterface $logger,
        private ValidatorInterface $validator,
        private ValidationHelper $validationHelper
    ) {}

    /**
     * Display contact form and handle form submission
     * 
     * GET: Renders the contact form page.
     * POST: Processes form data, sanitizes input, saves to database, and sends email notification.
     * 
     * @param Request $request HTTP request containing form data
     * @return Response Rendered contact form or redirect after submission
     */
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request): Response
    {
        $msg = new ContactMessage();
        $form = $this->createForm(ContactMessageType::class, $msg);
        $form->handleRequest($request);

        // Process form submission if valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Sanitize all user input to prevent XSS attacks before persisting
            $msg->setFirstName(InputSanitizer::sanitize($msg->getFirstName()));
            $msg->setLastName(InputSanitizer::sanitize($msg->getLastName()));
            $msg->setEmail(InputSanitizer::sanitize($msg->getEmail()));
            if ($msg->getPhone()) {
                $msg->setPhone(InputSanitizer::sanitize($msg->getPhone()));
            }
            $msg->setMessage(InputSanitizer::sanitize($msg->getMessage()));
            
            // Persist contact message to database
            $this->em->persist($msg);
            $this->em->flush();

            // Send email notification to admin (non-blocking - failures are logged but don't break the flow)
            try {
                $this->emailService->sendNotificationToAdmin(
                    $msg->getEmail(),
                    $msg->getFirstName() . ' ' . $msg->getLastName(),
                    $msg->getSubject(),
                    $msg->getMessage()
                );
            } catch (\Exception $e) {
                // Log error but don't prevent saving - email failure shouldn't block message storage
                $this->logger->error('Error sending contact notification: {error}', ['error' => $e->getMessage()]);
            }

            // Show success message and redirect to prevent duplicate submissions
            $this->addFlash('success', 'Merci! Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('pages/contact.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }

    /**
     * AJAX endpoint for contact form submission
     * 
     * Handles contact form submissions via AJAX requests.
     * Validates CSRF token before processing.
     * 
     * @param Request $request HTTP request containing form data
     * @param CsrfTokenManagerInterface $csrfTokenManager CSRF token manager
     * @return JsonResponse Success/error response
     */
    #[Route('/contact-ajax', name: 'app_contact_ajax', methods: ['POST'])]
    public function contactAjax(Request $request, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        // Validate CSRF token to prevent cross-site request forgery attacks
        $csrfToken = $request->request->get('_token') ?: $request->headers->get('X-CSRF-Token');
        if (!$csrfToken || !$csrfTokenManager->isTokenValid(new CsrfToken('submit', $csrfToken))) {
            $response = new \App\DTO\ApiResponseDTO(success: false, message: 'Token CSRF invalide');
            return $this->json($response->toArray(), 403);
        }
        
        return $this->handleContactAjax($request);
    }
    
    /**
     * Process AJAX contact form submission
     * 
     * Validates input, sanitizes data, saves to database, and sends email notification.
     * Includes XSS detection and comprehensive validation.
     * 
     * @param Request $request HTTP request containing form data
     * @return JsonResponse Success/error response with validation errors if any
     */
    private function handleContactAjax(Request $request): JsonResponse
    {
        // Ensure this is an AJAX request
        if (!$request->isXmlHttpRequest()) {
            $response = new \App\DTO\ApiResponseDTO(success: false, message: 'Requête invalide');
            return $this->json($response->toArray(), 400);
        }
        
        try {
            // Extract form data from request
            $firstName = InputSanitizer::sanitize($request->request->get('firstName', ''));
            $lastName = InputSanitizer::sanitize($request->request->get('lastName', ''));
            $email = InputSanitizer::sanitize($request->request->get('email', ''));
            $phone = InputSanitizer::sanitize($request->request->get('phone', ''));
            $subject = $request->request->get('subject', ''); // Subject doesn't need sanitization as it's from predefined choices
            $message = InputSanitizer::sanitize($request->request->get('message', ''));
            $consent = (bool)$request->request->get('consent', false);

            // Map to DTO and validate via Symfony Validator
            $dto = new ContactCreateRequest();
            $dto->firstName = $firstName;
            $dto->lastName = $lastName;
            $dto->email = $email;
            $dto->phone = $phone ?: null;
            $dto->subject = $subject;
            $dto->message = $message;
            $dto->consent = $consent;

            // Validate DTO
            $violations = $this->validator->validate($dto);
            if (count($violations) > 0) {
                $errors = $this->validationHelper->extractViolationMessages($violations);
                
                // Also check for XSS attempts (additional security layer)
                $xssErrors = [];
                if (InputSanitizer::containsXssAttempt($dto->firstName)) {
                    $xssErrors[] = 'Le prénom contient des éléments non autorisés';
                }
                if (InputSanitizer::containsXssAttempt($dto->lastName)) {
                    $xssErrors[] = 'Le nom contient des éléments non autorisés';
                }
                if (InputSanitizer::containsXssAttempt($dto->email)) {
                    $xssErrors[] = 'L\'email contient des éléments non autorisés';
                }
                if ($dto->phone && InputSanitizer::containsXssAttempt($dto->phone)) {
                    $xssErrors[] = 'Le numéro de téléphone contient des éléments non autorisés';
                }
                if (InputSanitizer::containsXssAttempt($dto->message)) {
                    $xssErrors[] = 'Le message contient des éléments non autorisés';
                }
                
                $allErrors = array_merge($errors, $xssErrors);
                $response = new \App\DTO\ApiResponseDTO(
                    success: false,
                    message: 'Erreur de validation. Veuillez vérifier vos données.',
                    errors: $allErrors
                );
                return $this->json($response->toArray(), 422);
            }

            // Additional XSS check after validation (defense in depth)
            $xssErrors = [];
            if (InputSanitizer::containsXssAttempt($dto->firstName)) {
                $xssErrors[] = 'Le prénom contient des éléments non autorisés';
            }
            if (InputSanitizer::containsXssAttempt($dto->lastName)) {
                $xssErrors[] = 'Le nom contient des éléments non autorisés';
            }
            if (InputSanitizer::containsXssAttempt($dto->email)) {
                $xssErrors[] = 'L\'email contient des éléments non autorisés';
            }
            if ($dto->phone && InputSanitizer::containsXssAttempt($dto->phone)) {
                $xssErrors[] = 'Le numéro de téléphone contient des éléments non autorisés';
            }
            if (InputSanitizer::containsXssAttempt($dto->message)) {
                $xssErrors[] = 'Le message contient des éléments non autorisés';
            }
            
            if (!empty($xssErrors)) {
                $response = new \App\DTO\ApiResponseDTO(
                    success: false,
                    message: 'Données invalides détectées',
                    errors: $xssErrors
                );
                return $this->json($response->toArray(), 400);
            }
            
            // Create and save contact message
            $contactMessage = new ContactMessage();
            $contactMessage->setFirstName($dto->firstName);
            $contactMessage->setLastName($dto->lastName);
            $contactMessage->setEmail($dto->email);
            $contactMessage->setPhone($dto->phone);
            $contactMessage->setSubject($dto->subject);
            $contactMessage->setMessage($dto->message);
            $contactMessage->setConsent($dto->consent);
            
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
                $this->logger->error('Error sending contact notification: {error}', ['error' => $e->getMessage()]);
            }
            
            $response = new \App\DTO\ApiResponseDTO(success: true, message: 'Merci! Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');
            return $this->json($response->toArray());
            
        } catch (\Exception $e) {
            $response = new \App\DTO\ApiResponseDTO(success: false, message: 'Une erreur est survenue lors de l\'envoi de votre message.');
            return $this->json($response->toArray(), 500);
        }
    }
}