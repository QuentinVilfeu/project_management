<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;

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