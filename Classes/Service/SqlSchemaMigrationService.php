<?php
namespace SJBR\StaticInfoTables\Service;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 *
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is an copy/paste driven development from the TYPO3 Core due to deprecated function for TYPO3 9LTS
 */
class SqlSchemaMigrationService
{

    /**
     * Reads the field definitions for the current database
     *
     * @return array Array with information about table.
     */
    public function getFieldDefinitions_database()
    {
        $total = [];
        $tempKeys = [];
        $tempKeysPrefix = [];
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName('Default');
        $statement = $connection->query('SHOW TABLE STATUS FROM `' . $connection->getDatabase() . '`');
        $tables = [];
        while ($theTable = $statement->fetch()) {
            $tables[$theTable['Name']] = $theTable;
        }
        foreach ($tables as $tableName => $tableStatus) {
            // Fields
            $statement = $connection->query('SHOW FULL COLUMNS FROM `' . $tableName . '`');
            $fieldInformation = [];
            while ($fieldRow = $statement->fetch()) {
                $fieldInformation[$fieldRow['Field']] = $fieldRow;
            }
            foreach ($fieldInformation as $fN => $fieldRow) {
                $total[$tableName]['fields'][$fN] = $this->assembleFieldDefinition($fieldRow);
            }
            // Keys
            $statement = $connection->query('SHOW KEYS FROM `' . $tableName . '`');
            $keyInformation = [];
            while ($keyRow = $statement->fetch()) {
                $keyInformation[] = $keyRow;
            }
            foreach ($keyInformation as $keyRow) {
                $keyName = $keyRow['Key_name'];
                $colName = $keyRow['Column_name'];
                if ($keyRow['Sub_part'] && $keyRow['Index_type'] !== 'SPATIAL') {
                    $colName .= '(' . $keyRow['Sub_part'] . ')';
                }
                $tempKeys[$tableName][$keyName][$keyRow['Seq_in_index']] = $colName;
                if ($keyName === 'PRIMARY') {
                    $prefix = 'PRIMARY KEY';
                } else {
                    if ($keyRow['Index_type'] === 'FULLTEXT') {
                        $prefix = 'FULLTEXT';
                    } elseif ($keyRow['Index_type'] === 'SPATIAL') {
                        $prefix = 'SPATIAL';
                    } elseif ($keyRow['Non_unique']) {
                        $prefix = 'KEY';
                    } else {
                        $prefix = 'UNIQUE';
                    }
                    $prefix .= ' ' . $keyName;
                }
                $tempKeysPrefix[$tableName][$keyName] = $prefix;
            }
            // Table status (storage engine, collation, etc.)
            if (is_array($tableStatus)) {
                $tableExtraFields = [
                    'Engine' => 'ENGINE',
                    'Collation' => 'COLLATE'
                ];
                foreach ($tableExtraFields as $mysqlKey => $internalKey) {
                    if (isset($tableStatus[$mysqlKey])) {
                        $total[$tableName]['extra'][$internalKey] = $tableStatus[$mysqlKey];
                    }
                }
            }
        }
        // Compile key information:
        if (!empty($tempKeys)) {
            foreach ($tempKeys as $table => $keyInf) {
                foreach ($keyInf as $kName => $index) {
                    ksort($index);
                    $total[$table]['keys'][$kName] = $tempKeysPrefix[$table][$kName] . ' (' . implode(',', $index) . ')';
                }
            }
        }
        return $total;
    }
}