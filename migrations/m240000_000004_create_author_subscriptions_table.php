<?php

namespace app\migrations;

use yii\db\Migration;

class m240000_000004_create_author_subscriptions_table extends Migration
{
    public function up(): void
    {
        $this->createTable('{{%author_subscriptions}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->null(),
            'phone' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-author_subscriptions-author_id',
            '{{%author_subscriptions}}',
            'author_id',
            '{{%authors}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-author_subscriptions-user_id',
            '{{%author_subscriptions}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    public function down(): void
    {
        $this->dropTable('{{%author_subscriptions}}');
    }
} 