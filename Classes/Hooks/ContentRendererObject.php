<?php
namespace KERN23\ContentDesigner\Hooks;
use \TYPO3\CMS\Core\Utility\GeneralUtility as GeneralUtility;

/**
 * Class/Function to extend the typoscript Data Object (fields/data) to access them
 *
 */
class ContentRendererObject implements \TYPO3\CMS\Frontend\ContentObject\ContentObjectPostInitHookInterface {
	public function postProcessContentObjectInitialization(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer &$pObject) {
		if ( isset($pObject->data['tx_contentdesigner_flexform']) ) {
			$objData = $pObject->data['tx_contentdesigner_flexform'];
		} elseif ( isset($GLOBALS['TSFE']->page['tx_contentdesigner_flexform']) ) {
			$objData = $GLOBALS['TSFE']->page['tx_contentdesigner_flexform'];
		} else return false;
		
		if ( empty($objData) ) return false;
		
		$ffh       = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\FlexFormService');
		$flexArray = $ffh->convertFlexFormContentToArray($objData);
		
		if ( is_array($flexArray['settings']['flexform']) ) {
			if ( $pObject->getCurrentTable() == 'pages' )
				$pObject->data = array_merge($pObject->data,$flexArray['settings']['flexform']);
			
			$GLOBALS['TSFE']->page = array_merge($GLOBALS['TSFE']->page,$flexArray['settings']['flexform']);
			unset($flexArray['settings']['flexform']);
		}
		
		if ( is_array($flexArray['settings']) ) {
			if ( $pObject->getCurrentTable() == 'pages' )
				$pObject->data = array_merge($pObject->data,$flexArray['settings']);
			$GLOBALS['TSFE']->page = array_merge($GLOBALS['TSFE']->page,$flexArray['settings']);
			unset($flexArray['settings']);
		}
		
		if ( sizeof($flexArray) > 0 ) {
			if ( $pObject->getCurrentTable() == 'pages' )
				$pObject->data = array_merge($pObject->data,$flexArray);
			
			$GLOBALS['TSFE']->page = array_merge($GLOBALS['TSFE']->page,$flexArray);
			unset($flexArray);
		}
	}
}

?>
