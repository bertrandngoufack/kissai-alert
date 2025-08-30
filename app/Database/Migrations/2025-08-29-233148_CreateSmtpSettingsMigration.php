<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSmtpSettingsMigration extends Migration
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
            ],
            'host' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
            ],
            'port' => [
                'type' => 'INT',
                'constraint' => 5,
                'default' => 587,
            ],
            'encryption' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'tls',
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'from_email' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
            ],
            'from_name' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
            ],
            'active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('smtp_settings');
    }

    public function down()
    {
        $this->forge->dropTable('smtp_settings', true);
    }
}
