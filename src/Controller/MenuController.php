<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MenuItemRepository;
use App\Repository\ReviewRepository;
use App\Repository\DrinkRepository;
use App\Entity\MenuItem;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Psr\Log\LoggerInterface;

/**
 * Public menu and dish detail pages.
 *
 * Notes:
 * - index() serializes MenuItem entities into lightweight arrays for the frontend JS.
 * - show() prepares dish detail data and uses lightweight queries for related items
 *   plus aggregate ratings to avoid heavy hydration.
 */
final class MenuController extends AbstractController
{
    #[Route('/menu', name: 'app_menu')]
    public function index(MenuItemRepository $menuItemRepository, DrinkRepository $drinkRepository, CacheManager $cacheManager, LoggerInterface $logger): Response
    {
        // Fetch all menu items from database
        // Using method with eager loading to load badges, tags and allergens
        $items = $menuItemRepository->findAllWithRelations();

        // Normalize entities for frontend (structure expected by static/js/menu.js)
        $menuItems = array_map(static function (MenuItem $item) use ($cacheManager, $logger): array {
            // Extract badges (e.g. names or slugs)
            $badges = [];
            if (method_exists($item, 'getBadges')) {
                foreach ($item->getBadges() as $badge) {
                    $badges[] = method_exists($badge, 'getName') ? $badge->getName() : (string) $badge;
                }
            }

            // Extract tags (e.g. technical codes for filtering)
            $tags = [];
            if (method_exists($item, 'getTags')) {
                foreach ($item->getTags() as $tag) {
                    $tags[] = method_exists($tag, 'getCode') ? $tag->getCode() : (string) $tag;
                }
            }

            // Resolve public image path
            // Images are stored in /static/img/menu/ (all menu items in one folder)
            $image = $item->getImage();
            if ($image) {
                // If it's just a filename from upload, prefix with menu folder
                if (
                    !str_starts_with($image, '/uploads/')
                    && !str_starts_with($image, '/assets/')
                    && !str_starts_with($image, '/static/')
                    && !str_starts_with($image, 'http')
                ) {
                    $image = '/static/img/menu/' . ltrim($image, '/');
                }
                // If path starts with 'assets/' or 'static/', make it absolute under public
                if (str_starts_with($image, 'assets/') || str_starts_with($image, 'static/')) {
                    $image = '/' . ltrim($image, '/');
                }
            }

            $normalizedImage = $image ? ltrim($image, '/') : null;
            // Use JPEG only for better hosting compatibility (WebP may not be supported)
            $imageJpegPath = $imageHeroPath = null;
            if ($normalizedImage) {
                try {
                    // Generate thumbnail (gallery_jpeg) for menu cards - 900x600 optimized size
                    $imageJpegPath = $cacheManager->getBrowserPath($normalizedImage, 'gallery_jpeg');
                    // Generate full size (hero_jpeg) for detail pages - 1920x1080
                    $imageHeroPath = $cacheManager->getBrowserPath($normalizedImage, 'hero_jpeg');
                } catch (\Throwable $e) {
                    $logger->warning('LiipImagine failed to generate menu image variant', [
                        'path' => $normalizedImage,
                        'error' => $e->getMessage(),
                    ]);
                    // If generation fails, imageJpegPath and imageHeroPath remain null
                    // JavaScript will fallback to original image
                }
            }

            return [
                // Force ID as string to match JS (strict comparisons)
                'id' => (string) $item->getId(),
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'price' => (float) $item->getPrice(),
                'category' => $item->getCategory(), // Expected values: entrees|plats|desserts|boissons
                'image' => $image,
                'image_original' => $image,
                // Thumbnail for menu cards (900x600) - fallback to original if generation fails
                'image_optimized' => $imageJpegPath,
                'image_webp' => null, // WebP disabled for hosting compatibility
                // Full size for detail pages (1920x1080) - fallback to original if generation fails
                'image_full' => $imageHeroPath ?? $image,
                'image_full_webp' => null, // WebP disabled for hosting compatibility
                'badges' => $badges,
                'tags' => $tags,
            ];
        }, $items);

        // Encode to JSON (without escaping Unicode characters and slashes)
        $menuItemsJson = json_encode($menuItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Fetch drinks and group by type
        $drinks = $drinkRepository->findAll();
        $drinksGrouped = [
            'vins' => [],
            'chaudes' => [],
            'bieres' => [],
            'fraiches' => [],
        ];
        foreach ($drinks as $drink) {
            $type = method_exists($drink, 'getType') ? $drink->getType() : 'autres';
            $entry = [
                'name' => method_exists($drink, 'getName') ? $drink->getName() : '',
                'price' => method_exists($drink, 'getPrice') ? $drink->getPrice() : '',
            ];
            if (!isset($drinksGrouped[$type])) {
                $drinksGrouped[$type] = [];
            }
            $drinksGrouped[$type][] = $entry;
        }
        $drinksJson = json_encode($drinksGrouped, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Render Menu page template and expose data to client side
        return $this->render('pages/menu.html.twig', [
            'menuItemsJson' => $menuItemsJson,
            'drinksJson' => $drinksJson,
            'seo_title' => 'Menu restaurant | Bistro Paris',
            'seo_description' => 'Consultez le menu complet du Bistro : plats méditerranéens, desserts gourmands et boissons sélectionnées.',
            'seo_og_description' => 'Une carte de saison, des produits frais et des recettes généreuses : découvrez le menu du Bistro.',
        ]);
    }

    #[Route('/dish/{id}', name: 'app_dish_detail', requirements: ['id' => '\\d+'])]
    public function show(MenuItem $item, MenuItemRepository $menuItemRepository, ReviewRepository $reviewRepository): Response
    {
        // Prepare structure for template
        $badges = [];
        foreach ($item->getBadges() as $badge) {
            $badges[] = method_exists($badge, 'getName') ? $badge->getName() : (string) $badge;
        }

        $allergens = [];
        if (method_exists($item, 'getAllergens')) {
            foreach ($item->getAllergens() as $allergen) {
                $allergens[] = method_exists($allergen, 'getName') ? $allergen->getName() : (string) $allergen;
            }
        }

        // Resolve public image path
        // Images are stored in /static/img/menu/ (all menu items in one folder)
        $image = $item->getImage();
        if ($image) {
            // If it's just a filename from upload, prefix with menu folder
            if (
                !str_starts_with($image, '/uploads/')
                && !str_starts_with($image, '/assets/')
                && !str_starts_with($image, '/static/')
                && !str_starts_with($image, 'http')
            ) {
                $image = '/static/img/menu/' . ltrim($image, '/');
            }
            // If path starts with 'assets/' or 'static/', make it absolute under public
            if (str_starts_with($image, 'assets/') || str_starts_with($image, 'static/')) {
                $image = '/' . ltrim($image, '/');
            }
        }

        // Related dishes (same category) - optimized lightweight query
        $related = $menuItemRepository->findRelatedForCard($item->getCategory(), (int) $item->getId(), 3);
        // Normalize image path as previously
        foreach ($related as &$rel) {
            $rImage = $rel['image'] ?? null;
            if ($rImage) {
                if (
                    !str_starts_with($rImage, '/uploads/')
                    && !str_starts_with($rImage, '/assets/')
                    && !str_starts_with($rImage, '/static/')
                    && !str_starts_with($rImage, 'http')
                ) {
                    // All menu images are in /static/img/menu/
                    $rImage = '/static/img/menu/' . ltrim($rImage, '/');
                }
                if (str_starts_with($rImage, 'assets/') || str_starts_with($rImage, 'static/')) {
                    $rImage = '/' . ltrim($rImage, '/');
                }
            }
            $rel['image'] = $rImage;
        }

        // Get ingredients as array
        $ingredients = [];
        if (method_exists($item, 'getIngredientsAsArray')) {
            $ingredients = $item->getIngredientsAsArray();
        }

        // Prepare prep time display
        $prepTimeDisplay = null;
        if ($item->getPrepTimeMin() && $item->getPrepTimeMax()) {
            $prepTimeDisplay = $item->getPrepTimeMin() . ' - ' . $item->getPrepTimeMax();
        } elseif ($item->getPrepTimeMin()) {
            $prepTimeDisplay = (string) $item->getPrepTimeMin();
        } elseif ($item->getPrepTimeMax()) {
            $prepTimeDisplay = (string) $item->getPrepTimeMax();
        }

        // Compute rating summary from approved dish reviews - optimized helper
        $approvedStats = $reviewRepository->getApprovedStatsForMenuItem((int) $item->getId());
        $ratingCount = $approvedStats['cnt'];
        $ratingAvg = $approvedStats['avg'];

        $rawDescription = strip_tags($item->getDescription() ?? '');
        $normalizedDescription = trim($rawDescription) !== ''
            ? trim($rawDescription)
            : sprintf('Découvrez %s, une spécialité du Bistro à Paris.', $item->getName());
        $shortDescription = mb_strlen($normalizedDescription) > 155
            ? mb_substr($normalizedDescription, 0, 152) . '...'
            : $normalizedDescription;

        return $this->render('pages/dish_detail.html.twig', [
            'item' => $item,
            'image' => $image,
            'badges' => $badges,
            'allergens' => $allergens,
            'ingredients' => $ingredients,
            'prepTimeDisplay' => $prepTimeDisplay,
            'related' => $related,
            'ratingCount' => $ratingCount,
            'ratingAvg' => $ratingAvg,
            'seo_title' => sprintf('%s | Bistro Paris', $item->getName()),
            'seo_description' => $shortDescription,
            'seo_og_description' => $shortDescription,
            'seo_image' => $image ?? null,
            'seo_og_type' => 'article',
        ]);
    }
}
