<?php

use chipmob\user\migrations\Migration;

class m140101_130000_create_action_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->driverName == 'mysql') $this->tableOptions = $this->tableOptions . ' ROW_FORMAT=COMPRESSED';

        $this->createTable('{{%user_action}}', [
            'id' => $this->bigPrimaryKey(),
            'user_id' => $this->integer()->notNull(),
            'type' => $this->tinyInteger()->notNull()->defaultValue(0),
            'ip' => $this->string(45),
            'ua'=> $this->text(),
            'created_at' => $this->integer()->notNull(),
        ], $this->tableOptions);

        $this->createIndex('{{%index_created_at}}', '{{%user_action}}', 'created_at');
        $this->addForeignKey('{{%fk_user_action_to_user}}', '{{%user_action}}', 'user_id', '{{%user}}', 'id', $this->cascade, $this->restrict);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_action}}');
    }
}
