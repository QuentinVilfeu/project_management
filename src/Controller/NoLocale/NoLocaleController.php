<?php

namespace App\Controller\NoLocale;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class NoLocaleController extends AbstractController
{
    #[Route(path: '/', name: 'app_landing_page_no_locale')]
    public function loginNoLocal(): Response
    {
        return $this->redirectToRoute('app_landing_page');
    }
}
