<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\Response;
use Config\Services;
use CodeIgniter\I18n\Time;

class ApiKeyAuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = Services::response();

        $authHeader = $request->getHeaderLine('Authorization');
        if ($authHeader === '') {
            return $this->unauthorized($response, 'Missing Authorization header.');
        }

        // Accept either "Basic <token>" or "Api-Key <token>"
        $token = null;
        if (stripos($authHeader, 'Basic ') === 0) {
            $token = trim(substr($authHeader, 6));
        } elseif (stripos($authHeader, 'Api-Key ') === 0) {
            $token = trim(substr($authHeader, 8));
        }

        if ($token === null || $token === '') {
            return $this->unauthorized($response, 'Invalid Authorization header.');
        }

        $db = db_connect();
        $builder = $db->table('api_keys');
        $row = $builder->select('*')->where('`key`', $token)->where('revoked', 0)->get()->getRowArray();

        if (!$row) {
            return $this->unauthorized($response, 'Invalid API Key.');
        }

        // Touch last_used_at
        $builder->where('id', $row['id'])->update(['last_used_at' => Time::now()->toDateTimeString()]);

        // Attach user id to request attributes for controllers
        $request->userId = $row['user_id'];
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }

    private function unauthorized(Response $response, string $message)
    {
        return $response->setStatusCode(401)->setJSON([
            'error' => [
                'code' => 401,
                'description' => $message,
            ],
        ]);
    }
}
