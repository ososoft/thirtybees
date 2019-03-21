<?php


class InformationSchemaBuilderCore
{
    /** @var DatabaseSchema */
    protected $schema;

    /**
     * @return DatabaseSchema
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getSchema()
    {
        if (! $this->schema) {
            $this->schema = new DatabaseSchema();
            $this->processTables();
        }
        return $this->schema;
    }


    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function processTables()
    {
        $connection = Db::getInstance();
        $tables = $connection->executeS("
            SELECT *
            FROM information_schema.TABLES t
            LEFT JOIN information_schema.COLLATION_CHARACTER_SET_APPLICABILITY c ON (c.collation_name = t.table_collation)
            WHERE t.TABLE_SCHEMA = database()
        ");
        foreach ($tables as $row) {
            $this->schema->addTable(
                (new TableSchema($row['TABLE_NAME']))
                    ->setEngine($row['ENGINE'])
                    ->setCharset($row['CHARACTER_SET_NAME'], $row['TABLE_COLLATION'])
            );
        }

        $columns = $connection->executeS('SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = database()');
        foreach ($columns as $row) {
            $columnName = $row['COLUMN_NAME'];
            $tableName = $row['TABLE_NAME'];
            $autoIncrement = strpos($row['EXTRA'], 'auto_increment') !== false;
            $isNullable = strtoupper($row['IS_NULLABLE']) === 'YES';
            $defaultValue = $row['COLUMN_DEFAULT'];
            if (is_null($defaultValue) && $isNullable) {
                $defaultValue = ObjectModel::DEFAULT_NULL;
            }
            $column = new ColumnSchema($columnName);;
            $column->setDataType($row['COLUMN_TYPE']);
            $column->setAutoIncrement($autoIncrement);
            $column->setNullable($isNullable);
            $column->setDefaultValue($defaultValue);
            $column->setCharset($row['CHARACTER_SET_NAME'], $row['COLLATION_NAME']);
            $this->schema->getTable($tableName)->addColumn($column);
        }

        $keys = $connection->executeS("
            SELECT s.TABLE_NAME, s.INDEX_NAME, t.CONSTRAINT_TYPE, s.COLUMN_NAME, s.SUB_PART
            FROM information_schema.STATISTICS s 
            LEFT JOIN information_schema.TABLE_CONSTRAINTS t ON (t.TABLE_SCHEMA = s.TABLE_SCHEMA AND t.TABLE_NAME = s.TABLE_NAME and t.CONSTRAINT_NAME = s.INDEX_NAME)
            WHERE s.TABLE_SCHEMA = database()
            ORDER BY s.TABLE_NAME, s.INDEX_NAME, s.SEQ_IN_INDEX
        ");
        foreach ($keys as $row) {
            $tableName = $row['TABLE_NAME'];
            $keyName = $row['INDEX_NAME'];
            $table = $this->schema->getTable($tableName);
            $key = $table->getKey($keyName);
            if (!$key) {
                $key = new TableKey($this->getKeyType($row['CONSTRAINT_TYPE']), $keyName);
                $table->addKey($key);
            }
            $key->addColumn($row['COLUMN_NAME'], $row['SUB_PART']);
        }
    }


    /**
     * @param $constraintType string database constranit type
     * @return int TableKey constant
     * @throws PrestaShopException
     */
    private function getKeyType($constraintType)
    {
       switch ($constraintType) {
           case 'PRIMARY KEY':
               return TableKey::PRIMARY_KEY;
           case 'UNIQUE':
               return TableKey::UNIQUE_KEY;
           case 'FOREIGN KEY':
               return TableKey::FOREIGN_KEY;
           default:
               return TableKey::KEY;
       }
    }
}
