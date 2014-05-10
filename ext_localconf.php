<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'KERN23.' . $_EXTKEY,
	'Pi1',
	array(
		'ContentRenderer' => 'show',	
	),
	// non-cacheable actions
	array(

	)
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
	mod.wizards.newContentElement.renderMode = tabs
');

// Form processing hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass'][] = 'EXT:content_designer/Classes/Hooks/BackendUtility.php:KERN23\\ContentDesigner\\Hooks\\BackendUtility';

// pageLayout Footer Content Hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] =
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Hooks/PageRenderer.php:KERN23\\ContentDesigner\\Hooks\\PageRenderer->addJSCSS';

// Explicit Allow Hook (if explicitADmode is on explicitAllow)
if ( $TYPO3_CONF_VARS['BE']['explicitADmode'] == 'explicitAllow' ) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['fetchGroups_postProcessing'][] = 'EXT:content_designer/Classes/Hooks/BackendUserAuthentication.php:KERN23\\ContentDesigner\\Hooks\\BackendUserAuthentication->fetchGroups_postProcessing';
}

// TSconfig Condition userFunc
if ( !function_exists(user_cdTSconfig) ) {
	function user_cdTSconfig($cmd) {
		return \KERN23\ContentDesigner\Utility\ConditionMatcher::evaluateCondition($cmd);
	}
}

// ContentRendererObject Hook
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['postInit'][] = 
    'EXT:content_designer/Classes/Hooks/ContentRendererObject.php:KERN23\\ContentDesigner\\Hooks\\ContentRendererObject';

?>