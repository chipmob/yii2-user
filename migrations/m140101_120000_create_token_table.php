<?php

use chipmob\user\migrations\Migration;

class m140101_120000_create_token_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_token}}', [
            'user_id' => $this->integer()->notNull(),
            'code' => $this->char(32)->notNull(),
            'type' => $this->tinyInteger()->notNull(),
            'expired_at' => $this->integer()->notNull(),
        ], $this->tableOptions);

        $this->createIndex('{{%unique_user_id_code_type}}', '{{%user_token}}', ['user_id', 'code', 'type'], true);
        $this->createIndex('{{%index_expired_at}}', '{{%user_token}}', 'expired_at');
        $this->addForeignKey('{{%fk_user_token_to_user}}', '{{%user_token}}', 'user_id', '{{%user}}', 'id', $this->cascade, $this->restrict);

        $this->execute("DROP PROCEDURE IF EXISTS `user_token_procedure_delete_expired`");
        $sql = <<<SQL
CREATE PROCEDURE `user_token_procedure_delete_expired`() NOT DETERMINISTIC CONTAINS SQL
DELETE FROM `user_token` WHERE FROM_UNIXTIME(expired_at) <= NOW()
SQL;
        $this->execute($sql);

        $this->execute("DROP EVENT IF EXISTS `user_token_event_delete_expired`");
        $sql = <<<SQL
CREATE EVENT `user_token_event_delete_expired` ON SCHEDULE EVERY 1 MINUTE STARTS CURDATE() ON COMPLETION NOT PRESERVE ENABLE
DO CALL user_token_procedure_delete_expired()
SQL;
        $this->execute($sql);
    }

    public function safeDown()
    {
        $this->execute("DROP EVENT IF EXISTS `user_token_event_delete_expired`");
        $this->execute("DROP PROCEDURE IF EXISTS `user_token_procedure_delete_expired`");

        $this->dropTable('{{%user_token}}');
    }
}
