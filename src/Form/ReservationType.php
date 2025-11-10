<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le prénom est requis'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le prénom ne peut contenir que des lettres, espaces et tirets'
                    ])
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom est requis'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le nom ne peut contenir que des lettres, espaces et tirets'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'email est requis'
                    ]),
                    new Assert\Email([
                        'message' => 'L\'email n\'est pas valide'
                    ])
                ]
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le numéro de téléphone est requis'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.\-]*\d{2}){4}$/',
                        'message' => 'Le numéro de téléphone n\'est pas valide'
                    ])
                ]
            ])
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date est requise'
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date de réservation ne peut pas être dans le passé'
                    ])
                ]
            ])
            ->add('time', ChoiceType::class, [
                'label' => 'Heure',
                'choices' => $this->generateTimeChoices(),
                'placeholder' => 'Choisir...',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'heure est requise'
                    ])
                ]
            ])
            ->add('guests', ChoiceType::class, [
                'label' => 'Nombre de personnes',
                'choices' => [
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
                ],
                'placeholder' => 'Nombre...',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nombre de personnes est requis'
                    ]),
                    new Assert\Range([
                        'min' => 1,
                        'max' => 20,
                        'notInRangeMessage' => 'Le nombre de personnes doit être entre {{ min }} et {{ max }}'
                    ])
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message (optionnel)',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 3,
                        'max' => 1000,
                        'minMessage' => 'Le message doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le message ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }

    private function generateTimeChoices(): array
    {
        $choices = [];
        // Generate time slots from 14:00 to 22:30 in 30-minute steps
        for ($hour = 14; $hour <= 22; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                if ($hour == 22 && $minute > 30) {
                    break; // Stop at 22:30
                }
                $timeString = sprintf('%02d:%02d', $hour, $minute);
                $choices[$timeString] = $timeString;
            }
        }
        return $choices;
    }
}
