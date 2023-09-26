<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;

class AccessMiddleware implements Middleware
{
    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '../../../../');
        $dotenv->load();
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = new Response();
        $authorizationHeader = $request->getHeaderLine('Authorization');

        $token = '';
        if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            $token = $matches[1];
        }

        try {
            $decoded = JWT::decode($token, new Key($_ENV['ACCESS_SECRET_KEY'], 'HS256'));
            $request = $request->withAttribute('tokenInfo', $decoded);
        } catch (InvalidArgumentException $e) {
            $response->getBody()
                ->write(json_encode(["statusCode" => 400, "message" => $e->getMessage()]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        } catch (DomainException $e) {
            $response->getBody()
                ->write(json_encode(["statusCode" => 422, "message" => $e->getMessage()]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(422);
        } catch (SignatureInvalidException $e) {
            $response->getBody()
                ->write(json_encode(["statusCode" => 401, "message" => $e->getMessage()]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        } catch (BeforeValidException $e) {
            $response->getBody()
                ->write(json_encode(["statusCode" => 401, "message" => $e->getMessage()]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        } catch (ExpiredException $e) {
            $response->getBody()
                ->write(json_encode(["statusCode" => 401, "message" => $e->getMessage()]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        } catch (UnexpectedValueException $e) {
            $response->getBody()
                ->write(json_encode(["statusCode" => 401, "message" => $e->getMessage()]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        return $handler->handle($request);
    }
}
