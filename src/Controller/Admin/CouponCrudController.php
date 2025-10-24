<?php

namespace App\Controller\Admin;

use App\Entity\Coupon;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class CouponCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Coupon::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Code promo')
            ->setEntityLabelInPlural('Codes promo')
            ->setPageTitle('index', 'Gestion des codes promo')
            ->setPageTitle('edit', 'Modifier le code promo')
            ->setPageTitle('new', 'Nouveau code promo')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('code', 'Code'))
            ->add(BooleanFilter::new('isActive', 'Actif'))
            ->add(DateTimeFilter::new('validFrom', 'Valide à partir de'))
            ->add(DateTimeFilter::new('validUntil', 'Valide jusqu\'au'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield TextField::new('code', 'Code promo')
            ->setRequired(true)
            ->setHelp('Code unique que les clients utiliseront (ex: PROMO2024)')
            ->setFormTypeOption('attr', ['style' => 'text-transform: uppercase;']);

        yield TextareaField::new('description', 'Description')
            ->setRequired(false)
            ->setHelp('Description interne du code promo (non visible par les clients)');

        yield ChoiceField::new('discountType', 'Type de réduction')
            ->setRequired(true)
            ->setChoices([
                'Pourcentage (%)' => Coupon::TYPE_PERCENTAGE,
                'Montant fixe (€)' => Coupon::TYPE_FIXED,
            ])
            ->renderAsBadges([
                Coupon::TYPE_PERCENTAGE => 'success',
                Coupon::TYPE_FIXED => 'primary',
            ]);

        yield NumberField::new('discountValue', 'Valeur de la réduction')
            ->setRequired(true)
            ->setHelp('Pour pourcentage: entrez un nombre entre 1-100. Pour montant fixe: entrez le montant en euros')
            ->setNumDecimals(2);

        yield MoneyField::new('minOrderAmount', 'Montant minimum de commande')
            ->setCurrency('EUR')
            ->setRequired(false)
            ->setHelp('Montant minimum de commande pour utiliser ce code (laisser vide si pas de minimum)');

        yield MoneyField::new('maxDiscount', 'Réduction maximum')
            ->setCurrency('EUR')
            ->setRequired(false)
            ->setHelp('Montant maximum de réduction (utile pour les pourcentages, laisser vide si pas de limite)');

        yield IntegerField::new('usageLimit', 'Limite d\'utilisation')
            ->setRequired(false)
            ->setHelp('Nombre maximum de fois que ce code peut être utilisé (laisser vide pour illimité)');

        yield IntegerField::new('usageCount', 'Nombre d\'utilisations')
            ->setPermission('ROLE_ADMIN')
            ->setHelp('Nombre de fois que ce code a été utilisé')
            ->hideOnForm();

        yield DateTimeField::new('validFrom', 'Valide à partir de')
            ->setRequired(false)
            ->setHelp('Date à partir de laquelle le code est valide (laisser vide pour immédiatement)');

        yield DateTimeField::new('validUntil', 'Valide jusqu\'au')
            ->setRequired(false)
            ->setHelp('Date jusqu\'à laquelle le code est valide (laisser vide pour illimité)');

        yield BooleanField::new('isActive', 'Actif')
            ->setHelp('Désactiver pour rendre le code inutilisable sans le supprimer');

        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm()
            ->setPermission('ROLE_ADMIN');

        yield DateTimeField::new('updatedAt', 'Modifié le')
            ->hideOnForm()
            ->setPermission('ROLE_ADMIN');
    }
}

