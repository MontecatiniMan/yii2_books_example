<?php

use yii\db\Migration;

class m240000_000004_create_author_subscriptions_table extends Migration
{
    public function up(): void
    {
        $this->createTable('{{%author_subscriptions}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->null(), // Может быть NULL для гостей
            'author_id' => $this->integer()->notNull(),
            'phone' => $this->string(20)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Индексы
        $this->createIndex('idx-author_subscriptions-user_id', '{{%author_subscriptions}}', 'user_id');
        $this->createIndex('idx-author_subscriptions-author_id', '{{%author_subscriptions}}', 'author_id');
        // Уникальный индекс только по телефону и автору (для гостей user_id = NULL)
        $this->createIndex('idx-author_subscriptions-phone-author', '{{%author_subscriptions}}', ['phone', 'author_id'], true);

        // Внешние ключи
        $this->addForeignKey('fk-author_subscriptions-user_id', '{{%author_subscriptions}}', 'user_id', '{{%user}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-author_subscriptions-author_id', '{{%author_subscriptions}}', 'author_id', '{{%authors}}', 'id', 'CASCADE');
    }

    public function down(): void
    {
        $this->dropForeignKey('fk-author_subscriptions-user_id', '{{%author_subscriptions}}');
        $this->dropForeignKey('fk-author_subscriptions-author_id', '{{%author_subscriptions}}');
        $this->dropTable('{{%author_subscriptions}}');
    }
} 