<?php

namespace chipmob\user\migrations;

use RuntimeException;

class Migration extends \yii\db\Migration
{
    protected ?string $tableOptions = null;
    protected string $restrict = 'RESTRICT';
    protected string $cascade = 'CASCADE';
    protected string $dbType;

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        switch ($this->db->driverName) {
            case 'mysql':
                $this->tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
                $this->dbType = 'mysql';
                break;
            case 'pgsql':
                $this->tableOptions = null;
                $this->dbType = 'pgsql';
                break;
            case 'dblib':
            case 'mssql':
            case 'sqlsrv':
                $this->restrict = 'NO ACTION';
                $this->tableOptions = null;
                $this->dbType = 'sqlsrv';
                break;
            default:
                throw new RuntimeException('Your database is not supported!');
        }
    }

    public function dropColumnConstraints(string $table, string $column)
    {
        $table = $this->db->schema->getRawTableName($table);
        $sql = <<<SQL
SELECT `name` FROM sys.default_constraints WHERE parent_object_id = object_id(:table) AND type = 'D' AND parent_column_id = (SELECT column_id FROM sys.columns WHERE object_id = object_id(:table) AND `name` = :column)
SQL;
        $cmd = $this->db->createCommand($sql, [':table' => $table, ':column' => $column]);
        $constraints = $cmd->queryAll();
        foreach ($constraints as $c) {
            $this->execute('ALTER TABLE ' . $this->db->quoteTableName($table) . ' DROP CONSTRAINT ' . $this->db->quoteColumnName($c['name']));
        }
    }
}
