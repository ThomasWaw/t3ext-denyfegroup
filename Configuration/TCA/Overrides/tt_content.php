<?php
defined('TYPO3_MODE') or die();

$additionalColumns = array(
    'fe_group_deny' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:denyfegroup/Resources/Private/Language/db.xml:fe_group_deny',
        'config' => array(
            'type' => 'select',
            'foreign_table' => 'fe_groups',
            'foreign_table_where' => 'ORDER BY fe_groups.title',
            'size' => 5,
            'minitems' => 0,
            'maxitems' => 50,
        )
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $additionalColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'fe_group_deny', '', 'after:fe_group');