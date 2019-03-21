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

class DifferentTableCharsetCore implements SchemaDifference
{
    private $table;
    private $currentTable;

    public function __construct(TableSchema $table, TableSchema $currentTable)
    {
        $this->table = $table;
        $this->currentTable = $currentTable;
    }

    public function describe()
    {
        return sprintf(
            Translate::getAdminTranslation('Table `%1$s` should use character set %2$s instead of %3$s'),
            $this->table->getName(),
            $this->table->getCharset() . '/' . $this->table->getCollate(),
            $this->currentTable->getCharset() . '/' . $this->currentTable->getCollate()
        );
    }
}