<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Politique de confidentialité (RGPD) controller.
 */
class PrivacyPolicyController extends AbstractController
{
    #[Route('/politique-confidentialite', name: 'app_privacy_policy')]
    public function __invoke(): Response
    {
        return $this->render('pages/privacy-policy.html.twig', [
            'seo_title' => 'Politique de confidentialité | Bistro Paris',
            'seo_description' => 'Découvrez comment Bistro collecte, utilise et protège vos données personnelles.',
            'seo_og_description' => 'Politique de confidentialité et informations RGPD du Bistro.',
            'seo_robots' => 'noindex,follow',
        ]);
    }
}


