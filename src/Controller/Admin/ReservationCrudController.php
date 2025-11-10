<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use App\Enum\ReservationStatus;
use App\Service\TableAvailabilityService;
use App\Service\SymfonyEmailService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_MODERATOR')]
class ReservationCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private SymfonyEmailService $emailService;

    public function __construct(EntityManagerInterface $entityManager, SymfonyEmailService $emailService)
    {
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
    }

    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Réservation')
            ->setEntityLabelInPlural('Réservations')
            ->setPageTitle('index', 'Gestion des réservations')
            ->setPageTitle('detail', 'Détails de la réservation')
            ->setPageTitle('edit', 'Modifier la réservation')
            ->setPageTitle('new', 'Nouvelle réservation')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig']);
    }

    public function configureFields(string $pageName): iterable
    {
        // Map French labels to ReservationStatus enum values for EasyAdmin form
        $statusChoices = [
            'En attente' => ReservationStatus::PENDING->value,
            'Confirmée' => ReservationStatus::CONFIRMED->value,
            'Annulée' => ReservationStatus::CANCELLED->value,
            'Réalisée' => ReservationStatus::COMPLETED->value,
            'Non venu' => ReservationStatus::NO_SHOW->value,
        ];

        $timeChoices = [];
        for ($hour = 14; $hour <= 22; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                if ($hour == 22 && $minute > 30) {
                    break;
                }
                $timeString = sprintf('%02d:%02d', $hour, $minute);
                $timeChoices[$timeString] = $timeString;
            }
        }

        return [
            IdField::new('id')
                ->hideOnForm()
                ->hideOnIndex(),
            
            TextField::new('firstName', 'Prénom')
                ->setRequired(true)
                ->setHelp('Prénom du client'),
            
            TextField::new('lastName', 'Nom')
                ->setRequired(true)
                ->setHelp('Nom de famille du client'),
            
            EmailField::new('email', 'Email')
                ->setRequired(true)
                ->setHelp('Adresse email du client'),
            
            TelephoneField::new('phone', 'Téléphone')
                ->setRequired(true)
                ->setHelp('Numéro de téléphone du client'),
            
            DateField::new('date', 'Date')
                ->setRequired(true)
                ->setFormat('dd/MM/yyyy')
                ->setHelp('Date de la réservation'),
            
            ChoiceField::new('time', 'Heure')
                ->setRequired(true)
                ->setChoices($timeChoices)
                ->setHelp('Heure de la réservation'),
            
            ChoiceField::new('guests', 'Nombre de personnes')
                ->setRequired(true)
                ->setChoices([
                    '1 personne' => 1,
                    '2 personnes' => 2,
                    '3 personnes' => 3,
                    '4 personnes' => 4,
                    '5 personnes' => 5,
                    '6 personnes' => 6,
                    '7 personnes' => 7,
                    '8 personnes' => 8,
                    '9 personnes' => 9,
                    '10+ personnes' => 10
                ])
                ->setHelp('Nombre de personnes pour la réservation'),
            
            TextareaField::new('message', 'Message')
                ->setRequired(false)
                ->setHelp('Message optionnel du client')
                ->setMaxLength(1000)
                ->setNumOfRows(4)
                ->formatValue(function ($value, $entity) use ($pageName) {
                    if (!$value) {
                        return '<em style="color: #6c757d; font-style: italic;">Aucun message</em>';
                    }
                    $text = nl2br(htmlspecialchars($value));
                    if ($pageName === Crud::PAGE_INDEX) {
                        $maxLength = 80;
                        if (mb_strlen($value) > $maxLength) {
                            return mb_substr($text, 0, $maxLength) . '...';
                        }
                    }
                    return $text;
                })
                ->renderAsHtml(),
            
            ChoiceField::new('status', 'Statut')
                ->setRequired(true)
                ->setChoices($statusChoices)
                ->setHelp('Statut de la réservation')
                ->setFormTypeOption('data', ReservationStatus::PENDING->value)
                ->setTemplatePath('admin/reservation/_status_badge.html.twig')
                ->renderAsBadges([
                    ReservationStatus::PENDING->value => 'warning',
                    ReservationStatus::CONFIRMED->value => 'success',
                    ReservationStatus::CANCELLED->value => 'danger',
                    ReservationStatus::COMPLETED->value => 'info',
                    ReservationStatus::NO_SHOW->value => 'secondary',
                ]),
            
            DateTimeField::new('createdAt', 'Date de création')
                ->hideOnForm()
                ->setFormat('dd/MM/yyyy HH:mm')
                ->setHelp('Date et heure de création de la réservation'),
            
            BooleanField::new('isConfirmed', 'Confirmée')
                ->hideOnForm()
                ->hideOnIndex()
                ->hideOnDetail()
                ->setDisabled(true)
                ->renderAsSwitch(false)
                ->setHelp('Indique si la réservation a été confirmée'),
            
            DateTimeField::new('confirmedAt', 'Date de confirmation')
                ->hideOnForm()
                ->setFormat('dd/MM/yyyy HH:mm')
                ->setHelp('Date et heure de confirmation'),
            
            TextareaField::new('confirmationMessage', 'Message de confirmation')
                ->hideOnForm()
                ->setHelp('Message de confirmation envoyé au client')
                ->formatValue(function ($value, $entity) use ($pageName) {
                    if (!$value) {
                        return '<em style="color: #6c757d; font-style: italic;">Aucune confirmation envoyée</em>';
                    }
                    $text = nl2br(htmlspecialchars($value));
                    if ($pageName === Crud::PAGE_INDEX) {
                        $maxLength = 80;
                        if (mb_strlen($value) > $maxLength) {
                            return mb_substr($text, 0, $maxLength) . '...';
                        }
                    }
                    return $text;
                })
                ->renderAsHtml(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $confirmAction = Action::new('confirm', 'Confirmer')
            ->setIcon('fa fa-check')
            ->setCssClass('btn btn-soft-success btn-sm')
            // Open confirm page first, then submit to same action (POST)
            ->linkToCrudAction('confirm')
            ->displayIf(function ($entity) {
                return $entity instanceof Reservation && 
                       $entity->getId() !== null && 
                       $entity->getStatus() === ReservationStatus::PENDING;
            });

        $cancelAction = Action::new('cancel', 'Annuler')
            ->setIcon('fa fa-times')
            ->setCssClass('btn btn-soft-warning btn-sm')
            ->linkToCrudAction('cancel')
            ->displayIf(function ($entity) {
                return $entity instanceof Reservation && 
                       $entity->getId() !== null && 
                       in_array($entity->getStatus(), [ReservationStatus::PENDING, ReservationStatus::CONFIRMED], true);
            });


        $actions = $actions
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $confirmAction)
            ->add(Crud::PAGE_INDEX, $cancelAction)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fa fa-plus')
                    ->setLabel('Nouvelle réservation')
                    ->setCssClass('btn btn-success');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fa fa-eye')
                    ->setLabel('Voir')
                    ->setCssClass('btn btn-soft-info btn-sm')
                    ->displayIf(function ($entity) {
                        return $entity instanceof Reservation && $entity->getId() !== null;
                    });
            });

        // Only admins can see delete action
        if ($this->isGranted('ROLE_ADMIN')) {
            $actions = $actions->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Supprimer')
                    // Keep required EasyAdmin class 'action-delete' so the modal submits the form
                    ->setCssClass('action-delete btn btn-soft-danger btn-sm')
                    ->displayIf(function ($entity) {
                        return $entity instanceof Reservation && $entity->getId() !== null;
                    });
            });
        } else {
            // Remove delete action completely for moderators
            $actions = $actions->remove(Crud::PAGE_INDEX, Action::DELETE);
        }

        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('firstName', 'Prénom'))
            ->add(TextFilter::new('lastName', 'Nom'))
            ->add(TextFilter::new('email', 'Email'))
            ->add(ChoiceFilter::new('status', 'Statut')
                ->setChoices([
                    'En attente' => ReservationStatus::PENDING->value,
                    'Confirmée' => ReservationStatus::CONFIRMED->value,
                    'Annulée' => ReservationStatus::CANCELLED->value,
                    'Réalisée' => ReservationStatus::COMPLETED->value,
                    'Non venu' => ReservationStatus::NO_SHOW->value,
                ]))
            ->add(DateTimeFilter::new('date', 'Date de réservation'))
            ->add(DateTimeFilter::new('createdAt', 'Date de création'));
    }

    

    #[Route('/backoffice/reservation/change-status', name: 'admin_reservation_change_status')]
    public function changeStatus(Request $request): Response
    {
        $entityId = $request->query->get('entityId');
        if (!$entityId) {
            $this->addFlash('error', 'ID de la réservation non trouvé.');
            return $this->redirectToRoute('admin');
        }

        /** @var Reservation|null $reservation */
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($entityId);
        if (!$reservation) {
            $this->addFlash('error', 'Réservation non trouvée.');
            return $this->redirectToRoute('admin');
        }

        // Status cycle for click: pending -> confirmed -> completed -> no_show -> pending
        // Use ReservationStatus enum for type safety
        $cycle = [ReservationStatus::PENDING, ReservationStatus::CONFIRMED, ReservationStatus::COMPLETED, ReservationStatus::NO_SHOW];
        $current = $reservation->getStatus() ?? ReservationStatus::PENDING;
        $currentIndex = array_search($current, $cycle, true);
        if ($currentIndex === false) {
            // If status is outside cycle (cancelled) — start with pending
            $next = ReservationStatus::PENDING;
        } else {
            $next = $cycle[($currentIndex + 1) % count($cycle)];
        }

        $reservation->setStatus($next);
        // Note: isConfirmed is automatically updated by setStatus() method in Reservation entity

        $this->entityManager->flush();

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('admin'));
    }

    /**
     * EasyAdmin inlined action to confirm the reservation.
     * This keeps EA context (and avoids template errors about `ea` variable).
     */
    public function confirmReservation(Request $request, TableAvailabilityService $availability): Response
    {
        $entityId = $request->query->get('entityId');
        if (!$entityId) {
            $this->addFlash('error', 'ID de la réservation non trouvé.');
            return $this->redirectToRoute('admin');
        }

        /** @var Reservation|null $reservation */
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($entityId);
        if (!$reservation) {
            $this->addFlash('error', 'Réservation non trouvée.');
            return $this->redirectToRoute('admin');
        }

        // Optional: final availability check on confirmation
        $isFree = $availability->isAvailable(
            $reservation->getDate(),
            (string) $reservation->getTime(),
            (int) $reservation->getGuests()
        );
        if (!$isFree) {
            $this->addFlash('danger', 'Pas de places suffisantes pour ce créneau.');
            return $this->redirectToRoute('admin');
        }

        $confirmationMessage = $request->request->get('confirmationMessage', 'Votre réservation est confirmée.');

        try {
            // Update reservation status using enum for type safety
            $reservation->setStatus(ReservationStatus::CONFIRMED);
            // Note: isConfirmed is automatically updated by setStatus() method in Reservation entity
            $reservation->setConfirmedAt(new \DateTimeImmutable());
            $reservation->setConfirmationMessage($confirmationMessage);

            $this->entityManager->flush();

            // Send confirmation email to client
            $clientName = $reservation->getFirstName().' '.$reservation->getLastName();
            $emailSubject = 'Confirmation de votre réservation - Le Trois Quarts';

            $emailSent = $this->emailService->sendReservationConfirmation(
                $reservation->getEmail(),
                $clientName,
                $emailSubject,
                $confirmationMessage,
                $reservation
            );

            if ($emailSent) {
                $this->addFlash('success', 'Réservation confirmée et email envoyé.');
            } else {
                $this->addFlash('warning', 'Réservation confirmée mais email non envoyé.');
            }

        } catch (\Throwable $e) {
            $this->addFlash('error', 'Erreur lors de la confirmation: '.$e->getMessage());
        }

        return $this->redirectToRoute('admin');
    }

    /**
     * Confirmation page (GET) + process (POST).
     */
    public function confirm(Request $request, TableAvailabilityService $availability): Response
    {
        $entityId = $request->query->get('entityId');
        if (!$entityId) {
            $this->addFlash('error', 'ID de la réservation non trouvé.');
            return $this->redirectToRoute('admin');
        }

        /** @var Reservation|null $reservation */
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($entityId);
        if (!$reservation) {
            $this->addFlash('error', 'Réservation non trouvée.');
            return $this->redirectToRoute('admin');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('reservation_confirm_'.$reservation->getId(), $request->request->get('_token'))) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('admin');
            }

            // Final availability check
            $isFree = $availability->isAvailable(
                $reservation->getDate(),
                (string) $reservation->getTime(),
                (int) $reservation->getGuests()
            );
            if (!$isFree) {
                $this->addFlash('danger', 'Pas de places suffisantes pour ce créneau.');
                return $this->redirectToRoute('admin');
            }

            $confirmationMessage = $request->request->get('confirmationMessage', 'Votre réservation est confirmée.');

            try {
                // Update reservation status using enum for type safety
                $reservation->setStatus(ReservationStatus::CONFIRMED);
                // Note: isConfirmed is automatically updated by setStatus() method in Reservation entity
                $reservation->setConfirmedAt(new \DateTimeImmutable());
                $reservation->setConfirmationMessage($confirmationMessage);
                $this->entityManager->flush();

                // Try to send email, but do not block on failures (sandbox limits, etc.)
                $clientName = $reservation->getFirstName().' '.$reservation->getLastName();
                $emailSubject = 'Confirmation de votre réservation - Le Trois Quarts';
                $emailSent = $this->emailService->sendReservationConfirmation(
                    $reservation->getEmail(),
                    $clientName,
                    $emailSubject,
                    $confirmationMessage,
                    $reservation
                );
                if ($emailSent) {
                    $this->addFlash('success', 'Réservation confirmée et email envoyé.');
                } else {
                    $this->addFlash('warning', 'Réservation confirmée 
                    (envoi d\'email non garanti: limite sandbox).');
                }

            } catch (\Throwable $e) {
                $this->addFlash('error', 'Erreur lors de la confirmation: '.$e->getMessage());
            }

            return $this->redirectToRoute('admin');
        }

        // GET: render confirmation page
        return $this->render('admin/reservation/confirm.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/admin/reservation/cancel', name: 'admin_reservation_cancel')]
    public function cancel(Request $request): Response
    {
        $entityId = $request->query->get('entityId');
        if (!$entityId) {
            $this->addFlash('error', 'ID de la réservation non trouvé.');
            return $this->redirectToRoute('admin');
        }

        $reservation = $this->entityManager->getRepository(Reservation::class)->find($entityId);
        if (!$reservation) {
            $this->addFlash('error', 'Réservation non trouvée.');
            return $this->redirectToRoute('admin');
        }

        try {
            // Update reservation status using enum for type safety
            $reservation->setStatus(ReservationStatus::CANCELLED);
            // Note: isConfirmed is automatically updated by setStatus() method in Reservation entity
            
            $this->entityManager->flush();
            
            // Send cancellation email to client
            $clientName = $reservation->getFirstName() . ' ' . $reservation->getLastName();
            $emailSubject = "Annulation de votre réservation - Le Trois Quarts";
            
            $emailSent = $this->emailService->sendReservationCancellation(
                $reservation->getEmail(),
                $clientName,
                $emailSubject,
                $reservation
            );
            
            if ($emailSent) {
                $this->addFlash('success', 'Réservation annulée et email envoyé avec succès !');
            } else {
                $this->addFlash('error', 'Réservation annulée mais erreur lors de l\'envoi de l\'email.');
            }
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'annulation: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin');
    }

    public function createEntity(string $entityFqcn): object
    {
        $reservation = new Reservation();
        
        // Set default values for new reservation using enum for type safety
        $reservation->setStatus(ReservationStatus::PENDING);
        $reservation->setIsConfirmed(false);
        
        // Set default date to today
        $reservation->setDate(new \DateTime());
        
        return $reservation;
    }
}
