<?php

use chipmob\user\migrations\Migration;

class m140101_140000_create_log_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->driverName == 'mysql') $this->tableOptions = $this->tableOptions . ' ROW_FORMAT=COMPRESSED';

        $this->createTable('{{%user_log}}', [
            'id' => $this->bigPrimaryKey(),
            'user_id' => $this->integer()->notNull(),
            'data' => $this->text(),
            'ip' => $this->string(45),
            'ua'=> $this->text(),
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
        ], $this->tableOptions);

        $this->createIndex('{{%index_created_at}}', '{{%user_log}}', 'created_at');
        $this->createIndex('{{%index_created_by}}', '{{%user_log}}', 'created_by');
        $this->addForeignKey('{{%fk_user_log_to_user}}', '{{%user_log}}', 'user_id', '{{%user}}', 'id', $this->cascade, $this->restrict);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_log}}');
    }
}
