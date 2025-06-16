<?php

namespace app\migrations;

use yii\db\Migration;

class m240000_000003_create_book_author_table extends Migration
{
    public function up(): void
    {
        $this->createTable('{{%book_author}}', [
            'book_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey('pk-book_author', '{{%book_author}}', ['book_id', 'author_id']);
        
        $this->addForeignKey(
            'fk-book_author-book_id',
            '{{%book_author}}',
            'book_id',
            '{{%books}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-book_author-author_id',
            '{{%book_author}}',
            'author_id',
            '{{%authors}}',
            'id',
            'CASCADE'
        );
    }

    public function down(): void
    {
        $this->dropTable('{{%book_author}}');
    }
} 