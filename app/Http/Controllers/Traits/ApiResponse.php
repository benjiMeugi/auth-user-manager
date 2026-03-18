<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Log;

trait ApiResponse
{
    /**
     * @param bool $success
     * @param int $code
     * @param string $error_message
     * @param mixed $errors
     * @param mixed $response_data
     */
    private function response($success, $code, $error_message = null, $errors = null, $response_data = null)
    {
        return [
            'success' => $success,
            'code' => $code,
            'body' => [
                'error_message' => $error_message,
                'errors' => $errors,
                'response_data' => $response_data,
                // 'response_data' => ["data" => $response_data],
            ]
        ];
    }

    /**
     * @param mixed $data
     */
    public function respondOk($data)
    {
        return $this->response(true, 200, null, null, $data);
    }

    /**
     * Method for converting thrown exceptions into http response
     *
     * @param \Exception $e
     * @param array|null $errors
     */
    protected function respondError(\Exception $e, $errors = null)
    {
        Log::error("Api Custom respondError " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine());
        Log::error("Api Custom respondError " . $e);
        return $this->response(false, 500, $e->getMessage(), $errors);
    }

    /**
     * Method for converting bad request to a json formatted response
     *
     * @param array $errors
     */
    protected function respondBadRequest($errors)
    {
        return $this->response(false, 422, null, $errors);
    }

    /**
     * @param $file
     * @param string $filename
     */
    public function respondFiledownload($file, $filename)
    {
        $header = [
            'Access-Control-Expose-Headers' => 'filename',
            'filename' => $filename,
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>  'attachment; filename="' . $filename . '"',
            // 'Content-Length' => strlen($pdf->output()),
        ];
        return response($file, 200, $header);
    }


    /**
     * @param $user
     * @param bool $authenticated
     * @param string $token
     * @param int $code
     */
    public function respondLogin($user, $authenticated, $token, $code, $error_message = "", $expire_at = null)
    {
        $data = [
            'user' => $user,
            'login_response' => [
                'authenticated' => $authenticated,
                'expire_at' => $expire_at,
                'double_auth_enabled' => false,
                'token' => $token
            ],
        ];

        return $this->response(false, $code, $error_message, null, $data);
    }
}
