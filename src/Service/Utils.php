<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\UserType;

class Utils
{
    public function turboStreamResponse(string $target, bool $reload = false): Response
    {    
        $streamContent = $reload
            ? '<turbo-stream action="visit" target="' . $target . '"></turbo-stream>'
            : $target;

        return new Response($streamContent, 200, ['Content-Type' => 'text/vnd.turbo-stream.html']);
    }
}