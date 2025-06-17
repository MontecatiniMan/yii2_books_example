<?php

use yii\db\Migration;

class m240000_000003_create_book_author_table extends Migration
{
    public function up(): void
    {
        $this->createTable('{{%book_author}}', [
            'id' => $this->primaryKey(),
            'book_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        // Индексы
        $this->createIndex('idx-book_author-book_id', '{{%book_author}}', 'book_id');
        $this->createIndex('idx-book_author-author_id', '{{%book_author}}', 'author_id');
        $this->createIndex('idx-book_author-unique', '{{%book_author}}', ['book_id', 'author_id'], true);

        // Внешние ключи
        $this->addForeignKey('fk-book_author-book_id', '{{%book_author}}', 'book_id', '{{%books}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-book_author-author_id', '{{%book_author}}', 'author_id', '{{%authors}}', 'id', 'CASCADE');
    }

    public function down(): void
    {
        $this->dropForeignKey('fk-book_author-book_id', '{{%book_author}}');
        $this->dropForeignKey('fk-book_author-author_id', '{{%book_author}}');
        $this->dropTable('{{%book_author}}');
    }
} 