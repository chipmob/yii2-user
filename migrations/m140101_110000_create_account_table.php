<?php

use chipmob\user\migrations\Migration;

class m140101_110000_create_account_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_account}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'provider' => $this->string()->notNull(),
            'client_id' => $this->string()->notNull(),
            'data' => $this->text(),
            'username' => $this->string(),
            'email' => $this->string(),
            'created_at' => $this->integer()->notNull(),
        ], $this->tableOptions);

        $this->createIndex('{{%unique_provider_client_id}}', '{{%user_account}}', ['provider', 'client_id'], true);
        $this->addForeignKey('{{%fk_user_account_to_user}}', '{{%user_account}}', 'user_id', '{{%user}}', 'id', $this->cascade, $this->restrict);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_account}}');
    }
}
