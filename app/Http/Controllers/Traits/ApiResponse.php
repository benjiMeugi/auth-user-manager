<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Trait ApiResponse
 * 
 * Centralise et uniformise toutes les réponses JSON renvoyées par les contrôleurs de l'API.
 * Permet de garantir un contrat de réponse prévisible pour les clients (frontend, autres microservices).
 */
trait ApiResponse
{
    /**
     * Méthode interne générique pour formater la structure JSON de toutes les réponses.
     *
     * @param bool        $success       Indique si la requête a réussi (true) ou échoué (false).
     * @param int         $code          Le code de statut HTTP (200, 201, 400, 401, 403, 404, 500, etc.).
     * @param string|null $error_message Message décrivant l'erreur (si applicable).
     * @param mixed|null  $errors        Détails des erreurs (ex: erreurs de validation de formulaire).
     * @param mixed|null  $response_data Données utiles retournées par l'API (payload principal).
     * @param string|null $message       Message informatif global (optionnel).
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    private function response($success, $code, $error_message = null, $errors = null, $response_data = null, $message = null)
    {
        return response()->json([
            'success' => $success,
            'code' => $code,
            'message' => $message,
            'body' => [
                'error_message' => $error_message,
                'errors' => $errors,
                'response_data' => $response_data,
            ]
        ], $code);
    }

    /**
     * Réponse HTTP 200 : Succès standard.
     * Utilisée pour les requêtes GET, PUT ou DELETE réussies.
     *
     * @param mixed       $data    Les données à retourner au client.
     * @param string|null $message Message optionnel de succès.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondOk($data, $message = null)
    {
        return $this->response(true, 200, null, null, $data, $message);
    }

    /**
     * Réponse HTTP 200 : Succès de l'authentification (Login).
     * Formate et retourne le token Sanctum, les informations de l'utilisateur et l'expiration.
     *
     * @param mixed       $userData  Les informations de l'utilisateur (ou profil formaté).
     * @param string      $token     Le token d'accès brut (plainTextToken).
     * @param string|null $expiresAt La date et l'heure d'expiration du token.
     * @param string      $message   Message de confirmation.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondLogin($userData, $token, $expiresAt = null, $message = 'Connexion réussie.')
    {
        return $this->response(true, 200, null, null, [
            'user' => $userData,
            'token' => $token,
            'expires_at' => $expiresAt,
        ], $message);
    }

    /**
     * Réponse HTTP 201 : Ressource créée avec succès.
     * Idéale après une requête POST de création (ex: création d'un utilisateur).
     *
     * @param mixed  $data    Les données de la ressource tout juste créée.
     * @param string $message Message descriptif.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondCreated($data, $message = 'Ressource créée avec succès.')
    {
        return $this->response(true, 201, null, null, $data, $message);
    }

    /**
     * Réponse HTTP 401 : Non authentifié.
     * Utilisée lorsque le token Sanctum est absent, expiré ou invalide.
     *
     * @param string $message Message d'explication.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondUnauthorized($message = 'Non authentifié.')
    {
        return $this->response(false, 401, $message, null, null, $message);
    }

    /**
     * Réponse HTTP 403 : Non autorisé.
     * Utilisée lorsque l'utilisateur est connecté mais ne possède pas la permission requise.
     *
     * @param string $message Message d'explication.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondForbidden($message = 'Action non autorisée.')
    {
        return $this->response(false, 403, $message, null, null, $message);
    }

    /**
     * Conversion d'une Exception attrapée en réponse d'erreur HTTP 500 (ou autre code personnalisé).
     * Enregistre automatiquement l'erreur dans les logs de Laravel pour le débogage.
     *
     * @param Exception  $e      L'exception levée.
     * @param mixed|null $errors Détails supplémentaires sur l'erreur.
     * @param int        $code   Code HTTP (par défaut 500).
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondError(Exception $e, $errors = null, $code = 500)
    {
        // Enregistrement des détails techniques dans les logs du serveur
        Log::error("Api Custom respondError: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        Log::error("Stack trace: " . $e);

        return $this->response(false, $code, $e->getMessage(), $errors);
    }

    /**
     * Réponse HTTP 422 : Erreur de validation (Bad Request).
     * Utilisée lorsque les données envoyées par le client ne respectent pas les règles du validateur.
     *
     * @param array  $errors  Liste des erreurs de validation par champ.
     * @param string $message Message global d'erreur.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondBadRequest($errors, $message = 'Données invalides.')
    {
        return $this->response(false, 422, $message, $errors);
    }

    /**
     * Réponse HTTP 404 : Ressource introuvable.
     * Utilisée lorsqu'un modèle ou un enregistrement recherché en base n'existe pas.
     *
     * @param mixed|null $id      L'identifiant de la ressource recherchée (optionnel).
     * @param string     $message Message personnalisé.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondNotFound($id = null, $message = 'Ressource introuvable.')
    {
        $errorMsg = $id ? "Ressource introuvable pour l'identifiant : {$id}" : $message;
        return $this->response(false, 404, $errorMsg);
    }

    /**
     * Réponse pour le téléchargement direct de fichiers binaires (ex: PDF, images, etc.).
     *
     * @param string $file     Le contenu binaire brut du fichier.
     * @param string $filename Le nom du fichier proposé au téléchargement côté client.
     * @param string $mimeType Le type MIME réel du fichier (ex: 'application/pdf').
     * 
     * @return \Illuminate\Http\Response
     */
    public function respondFileDownload($file, $filename, $mimeType = 'application/pdf')
    {
        $header = [
            'Access-Control-Expose-Headers' => 'filename',
            'filename' => $filename,
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($file),
        ];

        return response($file, 200, $header);
    }
}
