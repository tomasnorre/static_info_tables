<?php
namespace SJBR\StaticInfoTables\Utility;

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
 */

use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class InstallUtility implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var \SJBR\StaticInfoTables\Service\SqlSchemaMigrationService
     */
    protected $installToolSqlParser;

    /**
     * @param SJBR\StaticInfoTables\Service\SqlSchemaMigrationService $installToolSqlParser
     */
    public function injectInstallToolSqlParser(SJBR\StaticInfoTables\Service\SqlSchemaMigrationService $installToolSqlParser )
    {
        $this->installToolSqlParser = $installToolSqlParser;
    }

    /**
     * Update database / process db updates from ext_tables
     *
     * @param string $rawDefinitions The raw SQL statements from ext_tables.sql
     * @deprecated since TYPO3 v9, will be removed with TYPO3v10
     */
    public function updateDbWithExtTablesSql($rawDefinitions)
    {
        /** @var SqlReader $sqlReader */
        $sqlReader = GeneralUtility::makeInstance(SqlReader::class);
        $statements = $sqlReader->getCreateTableStatementArray($rawDefinitions);
        if (count($statements) !== 0) {
            $schemaMigrationService = GeneralUtility::makeInstance(SchemaMigrator::class);
            $schemaMigrationService->install($statements);
        }
    }

    /**
     * Import static SQL data (normally used for ext_tables_static+adt.sql)
     *
     * @param string $rawDefinitions
     */
    public function importStaticSql($rawDefinitions)
    {
        /** @var SqlReader $sqlReader */
        $sqlReader = GeneralUtility::makeInstance(SqlReader::class);
        $statements = $sqlReader->getStatementArray($rawDefinitions);

        /** @var SchemaMigrator $schemaMigrationService */
        $schemaMigrationService = GeneralUtility::makeInstance(SchemaMigrator::class);
        $schemaMigrationService->importStaticData($statements, true);
    }

}