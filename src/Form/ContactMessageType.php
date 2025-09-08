<?php
namespace App\Form;

use App\Entity\ContactMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    
    {
        $builder
            ->add('firstName', null, ['label' => 'Prénom'])
            ->add('lastName',  null, ['label' => 'Nom'])
            ->add('email',     null, ['label' => 'Email'])
            ->add('phone',     null, ['label' => 'Téléphone', 'required' => false])
            ->add('subject', ChoiceType::class, [
                'label' => 'Sujet',
                'placeholder' => 'Choisissez un sujet',
                'choices' => [
                    'Réservation'     => 'reservation',
                    'Commande'        => 'commande',
                    'Événement privé' => 'evenement_prive',
                    'Suggestion'      => 'suggestion',
                    'Réclamation'     => 'reclamation',
                    'Autre'           => 'autre',
                ],
                'required' => true,
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['rows' => 6],
            ])
            ->add('consent', CheckboxType::class, [
                'label' => "J'accepte d'être contacté par Le Trois Quarts concernant ma demande",
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ContactMessage::class]);
    }
}
