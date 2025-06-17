<?php

use yii\db\Migration;

class m240000_000001_create_authors_table extends Migration
{
    public function up(): void
    {
        $this->createTable('{{%authors}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    public function down(): void
    {
        $this->dropTable('{{%authors}}');
    }
} 