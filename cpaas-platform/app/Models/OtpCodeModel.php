<?php

namespace App\Models;

use CodeIgniter\Model;

class OtpCodeModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'otp_codes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\OtpCodeEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'recipient', 'code', 'alpha', 'length', 'attempts',
        'max_attempts', 'max_seconds_validity', 'app_id', 'status',
        'expires_at', 'created_at', 'updated_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function findPending(string $recipient, string $appId = ''): ?array
    {
        return $this->where('recipient', $recipient)
            ->where('app_id', $appId)
            ->where('status', 'pending')
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function incrementAttemptsAndGet(array $otp): array
    {
        $this->update($otp['id'], [
            'attempts' => ((int) $otp['attempts']) + 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->find($otp['id']);
    }
}
