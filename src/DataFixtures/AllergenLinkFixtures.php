<?php

namespace App\DataFixtures;

use App\Entity\Allergen;
use App\Entity\MenuItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Link existing MenuItem records with allergens based on dish content.
 * 
 * This fixture is idempotent and non-destructive:
 * - Finds items by name only, does NOT create new MenuItem rows
 * - Links allergens based on dish name and description keywords
 */
class AllergenLinkFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['allergen-links'];
    }

    public function load(ObjectManager $manager): void
    {
        $menuRepo = $manager->getRepository(MenuItem::class);
        $allergenRepo = $manager->getRepository(Allergen::class);

        // Get all allergens by code
        $allergensByCode = [];
        foreach ($allergenRepo->findAll() as $allergen) {
            $allergensByCode[$allergen->getCode()] = $allergen;
        }

        // Mapping: dish name => allergen codes
        $allergenMapping = [
            'Asperges Printemps à la Ricotta' => ['lactose', 'eggs'],
            'Œuf Mollet au Safran et Petits Pois' => ['eggs', 'lactose'],
            'Seiches Sautées à la Chermoula' => ['shellfish'],
            "Boulette d'agneau" => ['eggs'],
            "Galinette poêlée à l'ajo blanco" => ['fish', 'nuts'],
            'Sashimi de ventrèche de thon fumé' => ['fish'],
            'Magret de canard au fenouil confit' => [],
            'Velouté de châtaignes aux pleurottes' => ['lactose'],
            "Spaghettis à l'ail noir et parmesan" => ['gluten', 'lactose'],
            'Loup de mer aux pois chiches' => ['fish'],
            "Potimarron Rôti aux Saveurs d'Asie" => ['eggs', 'soy'],
            'Tartelette aux Marrons Suisses' => ['gluten', 'eggs', 'lactose'],
            'Tartelette Ricotta au Miel et Fraises' => ['gluten', 'eggs', 'lactose'],
            'Crémeux Yuzu aux Fruits Rouges' => ['eggs', 'nuts', 'lactose'],
            'Gaspacho Tomates et Melon' => [],
        ];

        $linked = 0;
        foreach ($allergenMapping as $dishName => $allergenCodes) {
            /** @var MenuItem|null $item */
            $item = $menuRepo->findOneBy(['name' => $dishName]);
            if (!$item) {
                continue; // Skip if dish doesn't exist
            }

            // Clear existing allergens for this item
            foreach ($item->getAllergens() as $existingAllergen) {
                $item->removeAllergen($existingAllergen);
            }

            // Add new allergens
            foreach ($allergenCodes as $code) {
                if (isset($allergensByCode[$code])) {
                    $item->addAllergen($allergensByCode[$code]);
                }
            }

            $manager->persist($item);
            $linked++;
        }

        $manager->flush();
    }
}
