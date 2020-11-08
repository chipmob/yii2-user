<?php

use chipmob\user\migrations\Migration;

class m140101_100000_init extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull(),
            'email' => $this->string()->notNull(),
            'password_hash' => $this->char(60)->notNull(),
            'auth_key' => $this->char(32)->notNull(),
            'access_token' => $this->char(32)->notNull(),
            'totp_key' => $this->char(16),
            'action_at' => $this->integer(),
            'confirmed_at' => $this->integer(),
            'blocked_at' => $this->integer(),
            'removed_at' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $this->tableOptions);

        $this->createIndex('{{%unique_username}}', '{{%user}}', 'username', true);
        $this->createIndex('{{%unique_email}}', '{{%user}}', 'email', true);
        $this->createIndex('{{%unique_access_token}}', '{{%user}}', 'access_token', true);
        $this->createIndex('{{%index_action_at}}', '{{%user}}', 'action_at');
        $this->createIndex('{{%index_confirmed_at}}', '{{%user}}', 'confirmed_at');
        $this->createIndex('{{%index_blocked_at}}', '{{%user}}', 'blocked_at');
        $this->createIndex('{{%index_removed_at}}', '{{%user}}', 'removed_at');
        $this->createIndex('{{%index_created_at}}', '{{%user}}', 'created_at');
        $this->createIndex('{{%index_updated_at}}', '{{%user}}', 'updated_at');

        $this->createTable('{{%user_profile}}', [
            'user_id' => $this->integer()->notNull()->append('PRIMARY KEY'),
            'name' => $this->string(),
            'public_email' => $this->string(),
            'location' => $this->string(),
            'website' => $this->string(),
            'timezone' => $this->string(40),
        ], $this->tableOptions);

        $this->addForeignKey('{{%fk_user_profile_to_user}}', '{{%user_profile}}', 'user_id', '{{%user}}', 'id', $this->cascade, $this->restrict);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_profile}}');
        $this->dropTable('{{%user}}');
    }
}
