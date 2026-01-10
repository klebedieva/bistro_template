<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Terms and Conditions (CGV) Controller
 * 
 * Simple controller that renders the terms and conditions page.
 * No business logic required - just displays static legal content.
 */
class CgvController extends AbstractController
{
    /**
     * Display terms and conditions page
     * 
     * Renders the CGV (Conditions Générales de Vente) page template.
     * 
     * @return Response Rendered CGV page
     */
    #[Route('/cgv', name: 'app_cgv')]
    public function index(): Response
    {
        return $this->render('pages/cgv.html.twig', [
            'seo_title' => 'Conditions générales de vente | Bistro',
            'seo_description' => 'Consultez les conditions générales de vente du restaurant Bistro : réservations, paiements, responsabilités et données personnelles.',
            'seo_og_description' => 'Toutes les informations légales et conditions de vente du Bistro.',
            'seo_robots' => 'noindex,follow',
        ]);
    }
}

