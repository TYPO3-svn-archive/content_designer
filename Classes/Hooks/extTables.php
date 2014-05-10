<?php
namespace KERN23\ContentDesigner\Hooks;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Hendrik Reimers <kontakt@kern23.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Extends dynamicly the TCA with the typoscript defined new content elements
 *
 * @author		Hendrik Reimers <kontakt@kern23.de>
 * @package		TYPO3
 * @subpackage	tx_contentdesigner
 */
class extTables {
	
	/**
	 * Extends the TCA CType
	 *
	 * @return void
	 */
	public function manipulateTCA($table = 'tt_content') {
		if ( \KERN23\ContentDesigner\Utility\Helper::compabilityCheck() != TRUE ) return FALSE;
		
		if ( $table == 'tt_content' ) {
			$elements = \KERN23\ContentDesigner\Utility\TypoScript::loadConfig($config, 'tx_contentdesigner', 0, $table.'.');
			self::renderItems($elements);
			
			$_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['content_designer']);
			if ( $_extConfig['altLabelField'] == 1 ) $GLOBALS['TCA'][$table]['ctrl']['label_userFunc'] = 'EXT:content_designer/Classes/Hooks/Label.php:KERN23\\ContentDesigner\\Hooks\\Label->getUserLabel';
			unset($_extConfig);
		} elseif ( $table == 'pages' ) {
			$pageUid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('edit');
			$pageUid = @key($pageUid['pages']);
			
			$elements = \KERN23\ContentDesigner\Utility\TypoScript::loadConfig($config, 'tx_contentdesigner', $pageUid, $table.'.', true);
			
			self::renderPageItems($elements);
		}
		
		unset($elements);
	}
	
	/**
	 * add cd Items to wizard items
	 *
	 * @param array $cdItems
	 * @return void
	 */
	public function renderItems(&$cdItems) {
		// we have grid elements to add
		if(count($cdItems)) {
			// Get Ext. Manager Config
			$_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['content_designer']);
			
			// If TSconfig Wizard adding enabled, create the Sheet
			$label = ( trim($_extConfig['sheetTitle']) == '' ) ? 'LLL:EXT:content_designer/Resources/Private/Language/locallang_be.xml:wizard.sheetTitle' : $_extConfig['sheetTitle'];
			
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
				mod.wizards.newContentElement.wizardItems.cd.header = '.$label.'
				#mod.wizards.newContentElement.wizardItems.cd.show = *
			');
			
			// traverse the elements and create wizard item for each element
			foreach($cdItems as $cdObjKey => $CTypeData) {
				$cdObjKey = substr($cdObjKey, 0, strlen($cdObjKey)-1);
				$CTypeData = $CTypeData['settings.'];
				
				// Add CType
				self::addContentDesignerPlugin($cdObjKey, $CTypeData);
				self::addContentDesignerItemToCType($cdObjKey, $CTypeData);
				
				// Add Wizard Items with TSconfig
				self::addContentDesignerToWizard($cdObjKey, $CTypeData);
			}
		}
	}
	
	/**
	 * add cd Items to wizard items
	 *
	 * @param array $cdItems
	 * @return void
	 */
	public function renderPageItems(&$cdItems) {
		// we have grid elements to add
		if(count($cdItems)) {
			$cdItem = &$cdItems['tx_contentdesigner_flexform.']['settings.'];
			
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', $cdItem['tca'], '', '');
			
			if ( $cdItem['cObjectFlexFile'] ) {
				// Use a FlexForm File
				$GLOBALS['TCA']['pages']['columns']['tx_contentdesigner_flexform']['config']['ds']['default'] = 'FILE:'.$cdItem['cObjectFlexFile'];
			} else {
				// Use base XML structure (the rest comes with TypoScript)
				$GLOBALS['TCA']['pages']['columns']['tx_contentdesigner_flexform']['config']['ds']['default'] = 'FILE:EXT:content_designer/Configuration/FlexForms/defaultPages.xml';
			}
		}
	}
	
	/**
	 * Add CD Items to Wizard
	 *
	 * @param string $cdObjKey
	 * @param array $cdItem
	 * @return void
	 */
	public function addContentDesignerToWizard($cdObjKey, $cdItem) {
		$icon  = (( strlen($cdItem['icon']) > 0 ) ? $cdItem['icon'] : \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('content_designer').'ce_wiz.gif');
		
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
			 mod.wizards.newContentElement.wizardItems.cd.show := addToList('.$cdObjKey.')
 			 mod.wizards.newContentElement.wizardItems.cd.elements {
				'.$cdObjKey.' {
					icon        = '.$icon.'
					title       = '.$cdItem['title'].'
					description = '.$cdItem['description'].'
					params      = &defVals[tt_content][CType]='.$cdObjKey.'
				}
			}
		');
	}
	
	/**
	 * add cd Items to CType
	 *
	 * @param string $cdObjKey
	 * @param array $cdItem
	 * @return void
	 */
	public function addContentDesignerItemToCType($cdObjKey, $cdItem, $table = 'tt_content') {
		$GLOBALS['TCA'][$table]['types'][$cdObjKey]['showitem'] = $cdItem['tca'];
		
		if ( $cdItem['cObjectFromPlugin'] ) {
			// Copy FlexConf from another Plugin (must be of type "plugin" eq. list)
			$GLOBALS['TCA'][$table]['columns']['pi_flexform']['config']['ds'][','.$cdObjKey] = $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds'][$cdItem['cObjectFromPlugin'].',list'];
		} elseif ( $cdItem['cObjectFlexFile'] ) {
			// Use a FlexForm File
			$GLOBALS['TCA'][$table]['columns']['pi_flexform']['config']['ds'][','.$cdObjKey] = 'FILE:'.$cdItem['cObjectFlexFile'];
		} else {
		    // Use base XML structure (the rest comes with TypoScript)
		    $GLOBALS['TCA'][$table]['columns']['pi_flexform']['config']['ds'][','.$cdObjKey] = 'FILE:EXT:content_designer/Configuration/FlexForms/default.xml';
		}
	}
	
	/**
	 * add cd Items as plugin
	 *
	 * @param string $cdObjKey
	 * @param array $cdItem
	 * @return void
	 */
	public function addContentDesignerPlugin($cdObjKey, $CTypeData) {
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
			array(
				\KERN23\ContentDesigner\Utility\Helper::translate($CTypeData['title']),
				$cdObjKey,
				(( strlen($CTypeData['iconSmall']) > 0 ) ? $CTypeData['iconSmall'] : \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('content_designer').'ext_icon.gif')
			),
			'CType'
		);
	}
}

?>