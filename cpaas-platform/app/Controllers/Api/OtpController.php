<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\OtpCodeModel;
use CodeIgniter\I18n\Time;

class OtpController extends ResourceController
{
    protected $format = 'json';

    private function rulesGenerate(): array
    {
        return [
            'recipient' => 'required|string',
            'alpha' => 'permit_empty|in_list[0,1,true,false]',
            'length' => 'permit_empty|integer|greater_than_equal_to[3]|less_than_equal_to[10]',
            'maxAttempts' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[10]',
            'maxSecondsValidity' => 'permit_empty|integer|greater_than_equal_to[30]|less_than_equal_to[6000]',
            'appId' => 'permit_empty|string|max_length[64]',
            'rejectIfPendingCode' => 'permit_empty|in_list[0,1,true,false]',
        ];
    }

    public function generate()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $validation = service('validation');
        $validation->setRules($this->rulesGenerate());
        if (!$validation->run($data)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        $recipient = (string) ($data['recipient'] ?? '');
        $alpha = filter_var($data['alpha'] ?? false, FILTER_VALIDATE_BOOL);
        $length = (int) ($data['length'] ?? 4);
        $maxAttempts = (int) ($data['maxAttempts'] ?? 3);
        $maxSecondsValidity = (int) ($data['maxSecondsValidity'] ?? 60);
        $appId = (string) ($data['appId'] ?? '');
        $rejectIfPending = filter_var($data['rejectIfPendingCode'] ?? false, FILTER_VALIDATE_BOOL);

        $model = new OtpCodeModel();
        $pending = $model->findPending($recipient, $appId);
        if ($rejectIfPending && $pending) {
            return $this->failResourceExists('Pending OTP exists');
        }

        // Generate code
        $characters = $alpha ? 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789' : '0123456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        $expiresAt = Time::now()->addSeconds($maxSecondsValidity)->toDateTimeString();

        $id = $model->insert([
            'user_id' => $this->request->userId ?? null,
            'recipient' => $recipient,
            'code' => $code,
            'alpha' => $alpha ? 1 : 0,
            'length' => $length,
            'attempts' => 0,
            'max_attempts' => $maxAttempts,
            'max_seconds_validity' => $maxSecondsValidity,
            'app_id' => $appId,
            'status' => 'pending',
            'expires_at' => $expiresAt,
        ]);

        $otp = $model->find($id);

        return $this->respond([
            'data' => [
                'recipient' => $otp['recipient'],
                'code' => $otp['code'],
                'attempts' => (int) $otp['attempts'],
                'maxAttempts' => (int) $otp['max_attempts'],
                'maxSecondsValidity' => (int) $otp['max_seconds_validity'],
                'appId' => $otp['app_id'],
                'createdAt' => $otp['created_at'],
                'updatedAt' => $otp['updated_at'],
                'expiresAt' => $otp['expires_at'],
            ],
        ]);
    }

    public function check()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $recipient = (string) ($data['recipient'] ?? '');
        $code = (string) ($data['code'] ?? '');
        $appId = (string) ($data['appId'] ?? '');
        if ($recipient === '' || $code === '') {
            return $this->failValidationErrors(['recipient' => 'required', 'code' => 'required']);
        }

        $model = new OtpCodeModel();
        $otp = $model->where('recipient', $recipient)
            ->where('app_id', $appId)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$otp) {
            return $this->respond(['data' => ['valid' => false, 'reason' => 'NotFound']]);
        }

        // Expired?
        if (Time::now()->isAfter(Time::parse($otp['expires_at']))) {
            $model->update($otp['id'], ['status' => 'expired']);
            return $this->respond(['data' => ['valid' => false, 'reason' => 'Expired', 'otp' => ['data' => $otp]]]);
        }

        // Max attempts?
        if ((int) $otp['attempts'] >= (int) $otp['max_attempts']) {
            $model->update($otp['id'], ['status' => 'blocked']);
            return $this->respond(['data' => ['valid' => false, 'reason' => 'MaxAttempts', 'otp' => ['data' => $otp]]]);
        }

        // Check code
        $otp = $model->incrementAttemptsAndGet($otp);
        $valid = hash_equals($otp['code'], $code);

        if ($valid) {
            $model->update($otp['id'], ['status' => 'validated']);
        }

        $reason = $valid ? 'Valid' : 'Invalid';
        return $this->respond([
            'data' => [
                'valid' => $valid,
                'reason' => $reason,
                'otp' => ['data' => $otp],
            ],
        ]);
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
