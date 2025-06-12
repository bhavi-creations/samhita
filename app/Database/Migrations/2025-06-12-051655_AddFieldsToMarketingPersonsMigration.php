<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToMarketingPersons extends Migration
{
    public function up()
    {
        // Add secondary_phone_num
        $fields_phone = [
            'secondary_phone_num' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true, // Secondary phone can be optional
                'after'      => 'phone', // Position it after existing phone
            ],
        ];
        $this->forge->addColumn('marketing_persons', $fields_phone);

        // Add image fields for documents
        $fields_images = [
            'aadhar_card_image' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'address', // Position after address
            ],
            'pan_card_image' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'aadhar_card_image',
            ],
            'driving_license_image' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'pan_card_image',
            ],
            'address_proof_image' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'driving_license_image',
            ],
        ];
        $this->forge->addColumn('marketing_persons', $fields_images);

        // You might want to remove this if you only need created_at to be auto set.
        // For example, if you want updated_at to update on every change.
        // If it's already set to CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, keep it that way.
        // This ensures updated_at truly reflects the last update time,
        // as your DESC query implies it's already auto-updating.
        // $this->forge->modifyColumn('marketing_persons', [
        //     'updated_at' => [
        //         'type' => 'DATETIME',
        //         'null' => false,
        //         'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        //     ],
        // ]);
    }

    public function down()
    {
        // Revert changes if migration is rolled back
        $this->forge->dropColumn('marketing_persons', 'secondary_phone_num');
        $this->forge->dropColumn('marketing_persons', 'aadhar_card_image');
        $this->forge->dropColumn('marketing_persons', 'pan_card_image');
        $this->forge->dropColumn('marketing_persons', 'driving_license_image');
        $this->forge->dropColumn('marketing_persons', 'address_proof_image');
    }
}