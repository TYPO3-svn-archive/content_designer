<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Setup static templates
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/default', 'CD: Include first!');

// Backend preview Item Hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][] =
    'EXT:content_designer/Classes/Hooks/DrawItem.php:KERN23\\ContentDesigner\\Hooks\\DrawItem';

// Register the content elements defined with SetupTS
\KERN23\ContentDesigner\Hooks\ExtTables::registerNewContentElements();

// Flexform to Pages
\KERN23\ContentDesigner\Hooks\ExtTables::extendPagesTca();

// Flexform to TtContent
\KERN23\ContentDesigner\Hooks\ExtTables::extendTtContentTca();

?>