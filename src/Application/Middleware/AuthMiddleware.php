<?php 

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        session_start();

        if (!isset($_SESSION['user'])) {
            $response = new SlimResponse();
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        return $handler->handle($request);
    }
}
