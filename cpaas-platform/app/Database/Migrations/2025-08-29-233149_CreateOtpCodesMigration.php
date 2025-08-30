<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOtpCodesMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'recipient' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
            ],
            'alpha' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'length' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 4,
            ],
            'attempts' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],
            'max_attempts' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 3,
            ],
            'max_seconds_validity' => [
                'type' => 'INT',
                'constraint' => 6,
                'default' => 60,
            ],
            'app_id' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'default' => '',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 'pending',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['recipient', 'app_id']);
        $this->forge->createTable('otp_codes');
    }

    public function down()
    {
        $this->forge->dropTable('otp_codes', true);
    }
}
