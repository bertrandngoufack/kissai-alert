<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\EmailLogModel;
use CodeIgniter\Email\Email;

class EmailController extends ResourceController
{
    protected $format = 'json';

    public function send()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $validation = service('validation');
        $validation->setRules([
            'to' => 'required|valid_email',
            'subject' => 'permit_empty|string',
            'body' => 'permit_empty|string',
            'smtp' => 'permit_empty',
        ]);

        if (!$validation->run($data)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        $email = service('email');

        if (!empty($data['smtp']) && is_array($data['smtp'])) {
            $config = [
                'protocol' => 'smtp',
                'SMTPHost' => $data['smtp']['host'] ?? '',
                'SMTPPort' => $data['smtp']['port'] ?? 587,
                'SMTPCrypto' => $data['smtp']['encryption'] ?? 'tls',
                'SMTPUser' => $data['smtp']['username'] ?? '',
                'SMTPPass' => $data['smtp']['password'] ?? '',
                'mailType' => 'html',
                'newline' => "\r\n",
            ];
            $email = new Email($config);
        }

        $email->setTo($data['to']);
        if (!empty($data['from'])) {
            $email->setFrom($data['from'], $data['fromName'] ?? null);
        }
        $email->setSubject($data['subject'] ?? '');
        $email->setMessage($data['body'] ?? '');

        $log = new EmailLogModel();
        $logId = $log->insert([
            'user_id' => $this->request->userId ?? null,
            'recipient' => $data['to'],
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'] ?? null,
            'status' => 'pending',
        ]);

        try {
            if (!$email->send()) {
                $error = $email->printDebugger(['headers', 'subject']);
                $log->update($logId, ['status' => 'error', 'error_message' => $error]);
                return $this->failServerError('Failed to send email');
            }
            $log->update($logId, ['status' => 'sent']);
            return $this->respond(['data' => ['status' => 'sent', 'id' => $logId]]);
        } catch (\Throwable $e) {
            $log->update($logId, ['status' => 'error', 'error_message' => $e->getMessage()]);
            return $this->failServerError($e->getMessage());
        }
    }
    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        //
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        //
    }

    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        //
    }

    /**
     * Return the editable properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        //
    }

    /**
     * Delete the designated resource object from the model.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        //
    }
}
