<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/default', 'CD: Include first!');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/example', 'CD: Simple Example');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/google_static_image/', 'CD: Google Static Image');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/google_maps/', 'CD: Google Maps');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/MediaElementJS/', 'CD: MediaelementJS');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/youtube/', 'CD: YouTube iFrame');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/gallery/', 'CD: Image Gallery');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/phpscript/', 'CD: PHP Script include');

// TCA Hook
\KERN23\ContentDesigner\Hooks\extTables::manipulateTCA('tt_content');

// DrawItem Hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][] = 
    'EXT:content_designer/Classes/Hooks/DrawItem.php:KERN23\\ContentDesigner\\Hooks\\DrawItem';

// Flexform to Pages
$TCA['pages']['columns'] += array(
	'tx_contentdesigner_flexform' => array(
		'label' => '',
		'exclude' => 1,
		'config' => array (
			'type' => 'flex',
			'ds_pointerField' => 'doktype'
		)
	)
);
\KERN23\ContentDesigner\Hooks\extTables::manipulateTCA('pages');

?>