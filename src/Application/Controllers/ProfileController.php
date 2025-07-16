<?php

namespace App\Application\Controllers;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProfileController
{
    private Twig $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function show(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'profile.twig', [
            'user' => $_SESSION['user'],
            'current_path' => $request->getUri()->getPath()
        ]);
    }
}