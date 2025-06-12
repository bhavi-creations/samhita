<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockOutTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'product_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'null'           => false,
            ],
            'quantity_out' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'null'           => false,
            ],
            'transaction_type' => [
                'type'           => 'VARCHAR',
                'constraint'     => '50',
                'null'           => false,
                'comment'        => 'e.g., marketing_distribution, direct_sale, damage_loss',
            ],
            'transaction_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'null'           => false,
                'comment'        => 'ID from the specific transaction table (e.g., marketing_distribution.id)',
            ],
            'issued_date' => [
                'type'           => 'DATE',
                'null'           => false,
            ],
            'notes' => [
                'type'           => 'TEXT',
                'null'           => true,
            ],
            'created_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'updated_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        // This line creates a foreign key relationship to your 'products' table.
        // Ensure your 'products' table exists and has an 'id' primary key.
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('stock_out');
    }

    public function down()
    {
        $this->forge->dropTable('stock_out');
    }
}