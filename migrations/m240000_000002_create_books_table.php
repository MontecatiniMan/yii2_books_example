<?php

namespace app\migrations;

use yii\db\Migration;

class m240000_000002_create_books_table extends Migration
{
    public function up(): void
    {
        $this->createTable('{{%books}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'description' => $this->text(),
            'isbn' => $this->string(13)->unique(),
            'publication_year' => $this->integer()->notNull(),
            'cover_image' => $this->string(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    public function down(): void
    {
        $this->dropTable('{{%books}}');
    }
} 