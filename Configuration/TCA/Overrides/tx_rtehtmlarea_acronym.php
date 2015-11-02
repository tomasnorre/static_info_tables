<?php
defined('TYPO3_MODE') or die();
if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getNumericTypo3Version()) > 7005000
	&& \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rtehtmlarea')
) {
	$additionalColumns = [
		'static_lang_isocode' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rtehtmlarea/Resources/Private/Language/locallang_db.xlf:tx_rtehtmlarea_acronym.static_lang_isocode',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					['', 0],
				],
				'foreign_table' => 'static_languages',
				'foreign_table_where' => 'ORDER BY static_languages.lg_name_en',
				'itemsProcFunc' => SJBR\StaticInfoTables\Hook\Backend\Form\FormDataProvider\TcaSelectItemsProcessor::class. '->translateLanguagesSelector',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'wizards' => [
					'suggest' => [
						'type' => 'suggest',
						'default' => [
							'receiverClass' => SJBR\StaticInfoTables\Hook\Backend\Form\Wizard\SuggestReceiver::class
						]
					]
				]
			]
		]
	];

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tx_rtehtmlarea_acronym', $additionalColumns);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tx_rtehtmlarea_acronym', 'static_lang_isocode');
}