<?php
/**
 * Copyright (C) 2018 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <contact@thirtybees.com>
 * @copyright 2018 thirty bees
 * @license   Open Software License (OSL 3.0)
 */

class TableSchemaCore
{
    /**
     * @var string table name
     */
    protected $name;

    /**
     * @var string database engine
     */
    protected $engine;

    /**
     * @var string default character set
     */
    protected $charset;

    /**
     * @var string default character collation
     */
    protected $collate;

    /**
     * @var ColumnSchema[] table columns
     */
    protected $columns;

    /**
     * @var TableKey[] table keys
     */
    protected $keys;

    /**
     * TableSchemaCore constructor.
     *
     * @param $name string name of the database table
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->columns = [];
        $this->keys = [];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUnprefixedName()
    {
        return Tools::strReplaceFirst(_DB_PREFIX_, '', $this->name);
    }

    /**
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param $engine
     * @return $this
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @return string
     */
    public function getCollate()
    {
        return $this->collate;
    }

    /**
     * @param string $defaultCharset
     * @param string $defaultCollate
     * @return TableSchemaCore
     */
    public function setCharset($defaultCharset, $defaultCollate)
    {
        $this->charset = $defaultCharset;
        $this->collate = $defaultCollate;
        return $this;
    }

    /**
     * @param ColumnSchema $column
     * @return TableSchemaCore
     */
    public function addColumn(ColumnSchema $column)
    {
        if (! $this->hasColumn($column->getName())) {
            $this->columns[] = $column;
        }
        return $this;
    }

    /**
     * @return ColumnSchema[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param $columns ColumnSchema[]
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * @param $columnNames
     */
    public function reorderColumns($columnNames)
    {
        $missing = array_values(array_diff($this->getColumnNames(), $columnNames));
        $order = array_merge($columnNames, $missing);

        usort($this->columns, function(ColumnSchema $a, ColumnSchema $b) use ($order) {
            $pos1 = (int)array_search($a->getName(), $order);
            $pos2 = (int)array_search($b->getName(), $order);
            return $pos1 - $pos2;
        });
    }

    /**
     * @return ColumnSchema[]
     */
    public function getColumnNames()
    {
        return array_map(function(ColumnSchema $column) {
            return $column->getName();
        }, $this->columns);
    }

    /**
     * @param $columnName
     * @return bool
     */
    public function hasColumn($columnName)
    {
        return !!$this->getColumn($columnName);
    }

    /**
     * @param $columnName string name of column
     * @return ColumnSchema | null
     */
    public function getColumn($columnName)
    {
        foreach ($this->columns as $column) {
            if ($column->getName() === $columnName) {
                return $column;
            }
        }
        return null;
    }
    /**
     * @param $keyName string name of key
     * @return TableKey | null
     */
    public function getKey($keyName)
    {
        if ($this->hasKey($keyName)) {
            return $this->keys[$keyName];
        }
        return null;
    }

    /**
     * @param $keyName
     * @return bool
     */
    public function hasKey($keyName)
    {
        return isset($this->keys[$keyName]);
    }

    /**
     * @param TableKey $key
     * @return $this
     */
    public function addKey(TableKey $key)
    {
        $this->keys[$key->getName()] = $key;
        return $this;
    }

    /**
     * @return TableKey[]
     */
    public function getKeys()
    {
        return $this->keys;
    }

}
