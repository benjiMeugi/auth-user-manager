<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
            
        // Intercepte et formate toutes les erreurs de l'API au format JSON uniformisé
        $exceptions->render(function (Throwable $e, Request $request) {
            
            // S'applique uniquement aux routes de l'API ou aux requêtes exigeant du JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                
                // 1. Route [login] non définie (sécurité anti-crash si un middleware cherche la route web de connexion)
                if ($e instanceof RouteNotFoundException && str_contains($e->getMessage(), 'login')) {
                    return response()->json([
                        'success' => false,
                        'code' => 401,
                        'message' => 'Non authentifié.',
                        'body' => [
                            'error_message' => 'Route [login] not defined or token missing.',
                            'errors' => null,
                            'response_data' => null,
                        ]
                    ], 401);
                }

                // 2. Non authentifié (Token Sanctum manquant, expiré ou invalide)
                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'success' => false,
                        'code' => 401,
                        'message' => 'Non authentifié. Veuillez fournir un token valide.',
                        'body' => [
                            'error_message' => $e->getMessage(),
                            'errors' => null,
                            'response_data' => null,
                        ]
                    ], 401);
                }

                // 3. Non autorisé (Permissions insuffisantes / Gate ou Can refusé)
                if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                    return response()->json([
                        'success' => false,
                        'code' => 403,
                        'message' => "Action non autorisée. Vous n'avez pas la permission requise.",
                        'body' => [
                            'error_message' => $e->getMessage(),
                            'errors' => null,
                            'response_data' => null,
                        ]
                    ], 403);
                }

                // 4. Ressource introuvable (404)
                if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
                    return response()->json([
                        'success' => false,
                        'code' => 404,
                        'message' => 'Ressource introuvable.',
                        'body' => [
                            'error_message' => $e->getMessage(),
                            'errors' => null,
                            'response_data' => null,
                        ]
                    ], 404);
                }

                // 4bis. Méthode HTTP non autorisée (405)
                if ($e instanceof MethodNotAllowedHttpException) {
                    return response()->json([
                        'success' => false,
                        'code' => 405,
                        'message' => 'Méthode HTTP non autorisée pour cette route.',
                        'body' => [
                            'error_message' => $e->getMessage(),
                            'errors' => null,
                            'response_data' => null,
                        ]
                    ], 405);
                }

                // 5. Trop de tentatives / Rate Limiting (429)
                if ($e instanceof TooManyRequestsHttpException) {
                    return response()->json([
                        'success' => false,
                        'code' => 429,
                        'message' => 'Trop de tentatives. Veuillez réessayer plus tard.',
                        'body' => [
                            'error_message' => $e->getMessage(),
                            'errors' => null,
                            'response_data' => null,
                        ]
                    ], 429);
                }

                // 5. Erreur interne du serveur (500) par défaut pour les autres exceptions
                return response()->json([
                    'success' => false,
                    'code' => 500,
                    'message' => 'Erreur interne du serveur.',
                    'body' => [
                        'error_message' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue.',
                        'errors' => null,
                        'response_data' => null,
                    ]
                ], 500);
            }
        
        });
    })->create();
