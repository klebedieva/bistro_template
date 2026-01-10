<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Mentions légales controller.
 */
class MentionsLegalesController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function __invoke(): Response
    {
        return $this->render('pages/mentions-legales.html.twig', [
            'seo_title' => 'Mentions légales | Bistro Paris',
            'seo_description' => 'Informations légales, éditeur du site, responsable de publication et hébergeur du Bistro.',
            'seo_og_description' => 'Mentions légales et informations réglementaires du Bistro.',
            'seo_robots' => 'noindex,follow',
        ]);
    }
}


