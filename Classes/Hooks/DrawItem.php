<?php

namespace KERN23\ContentDesigner\Hooks;

/**
 * Class/Function which renders the preview in the page module
 *
 */
class DrawItem implements \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface {

	private $cObj;
	protected $tsSetup;
	
	/**
	 * Preprocesses the preview rendering of a content element.
	 *
	 * @param	tx_cms_layout	$parentObject:  Calling parent object
	 * @param	boolean         $drawItem:      Whether to draw the item using the default functionalities
	 * @param	string	        $headerContent: Header content
	 * @param	string	        $itemContent:   Item content
	 * @param	array		$row:           Record row of tt_content
	 * @return	void
	 */
	public function preProcess(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
		// If Content Designer Element render the preview
		$this->renderPreview($parentObject, $drawItem, $headerContent, $itemContent, $row);
		
		// Drag and Drop check
		$this->disableDragDrop($parentObject, $drawItem, $headerContent, $itemContent, $row);
	}
	
	/**
	 * Modifies the Element to disable Dragging for cols
	 *
	 * @param	tx_cms_layout	$parentObject:  Calling parent object
	 * @param	boolean         $drawItem:      Whether to draw the item using the default functionalities
	 * @param	string	        $headerContent: Header content
	 * @param	string	        $itemContent:   Item content
	 * @param	array		$row:           Record row of tt_content
	 * @return	void
	 */
	private function disableDragDrop(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
		$pageUid         = $parentObject->pageRecord['uid'];
		$pageTSconfigStr = $parentObject->pageRecord['TSconfig'];
		$tsParser        = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Configuration\\TsConfigParser');
		$TSconfig        = $tsParser->parseTSconfig($pageTSconfigStr, 'PAGES', $pageUid);
		unset($pageUid, $pageTSconfigStr, $tsParser);
		
		if ( sizeof($TSconfig['TSconfig']['TCEFORM.']['disableDragDrop.']['CType.']) > 0 ) {
			foreach ( $TSconfig['TSconfig']['TCEFORM.']['disableDragDrop.']['CType.'] as $CType => $colList ) {
				if ( $CType == $row['CType'] ) $headerContent = '<span class="disableDragDrop" data-uid="'.$row['uid'].'" data-cols="'.$colList.'"></span>'.$headerContent;
			}
		} else return FALSE;
	}
	
	/**
	 * Modifies the Element to disable Dragging for cols
	 *
	 * @param	tx_cms_layout	$parentObject:  Calling parent object
	 * @param	boolean         $drawItem:      Whether to draw the item using the default functionalities
	 * @param	string	        $headerContent: Header content
	 * @param	string	        $itemContent:   Item content
	 * @param	array			$row:           Record row of tt_content
	 * @return	void
	 */
	private function renderPreview(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
		// Preview of content_designer elements
		$expr = '/^tx_contentdesigner_(.*)$/si';
		if ( preg_match($expr, $row['CType'], $match) ) {
			// Load the TypoScript Config
			$config         = array('row' => &$row);
			$arr_list        = \KERN23\ContentDesigner\Utility\TypoScript::loadConfig($config, 'tx_contentdesigner', $row['pid']);
			$this->tsSetup  = $arr_list[$row['CType'].'.'];
			unset($arr_list, $config);
			
			if ( $this->tsSetup['settings.']['disableDefaultDrawItem'] == 1 ) {
				$drawItem = FALSE;
			}
			
			// Render the preview
			$this->getContentElementPreview($match[1], $row, $headerContent, $itemContent);
			
			// Link the preview content
			$itemContent = $parentObject->linkEditContent($itemContent, $row);
		}
	}
 
	/**
	 * Preview of a content element *_pi1.
	 */
	private function getContentElementPreview($tscType, &$row, &$headerContent, &$itemContent) {
		// Load the Field Configuration for the current selected Object
		if ( is_array($this->tsSetup['settings.']['previewObj.']) ) {
			$objType    = $this->tsSetup['settings.']['previewObj'];
			$objArray   = $this->tsSetup['settings.']['previewObj.'];
			
			$CTypeTitle = \KERN23\ContentDesigner\Utility\Helper::translate($this->tsSetup['settings.']['title']);
			
			$conf       = $this->tsSetup['settings.'];
		} else return false;
		
		// Flexform rendern
		$ffh = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\FlexFormService'); // Static call not axcepted in 6.1.1
		$flexFormData = $ffh->convertFlexFormContentToArray($row['pi_flexform']);
		
		// Flex Data laden
		$cObjAr = array();
		if ( is_array($conf['cObject.']) && sizeof($conf['cObject.']) > 0 ) {
			// TS cObject als Daten Zuweisungsvorlage nehmen
			foreach ( array_keys($conf['cObject.']) as $sheet ) {
				$sheet = substr($sheet,0,strlen($sheet)-1);
				foreach ( array_keys($conf['cObject.'][$sheet.'.']['el.']) as $fieldKey ) {
					$fieldKey = substr($fieldKey,0,strlen($fieldKey)-1);
					$cObjAr[$fieldKey] = $flexFormData['settings']['flexform'][$fieldKey];
				}
			}
			
		} elseif ( isset($conf['cObjectFlexFile']) || isset($conf['cObjectFromPlugin']) ) {
			if ( strlen($conf['cObjectFromPlugin']) > 0 ) $conf['cObjectFlexFile']  = &$conf['cObjectFromPlugin'];
			
			// Load Flexfile
			$flexDefinition = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array(file_get_contents(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($conf['cObjectFlexFile'])));
			
			// Flexform Datei als Daten Zuweisungsvorlage nehmen
			if ( is_array($flexDefinition) && sizeof($flexDefinition) > 0 ) {
				foreach ( $flexDefinition['sheets'] as $sheet ) {
					foreach ( array_keys($sheet['ROOT']['el']) as $fieldKey ) {
						if ( preg_match("/^settings\.flexform\.(.*[^\.])$/i",$fieldKey,$m) ) {
							$cObjAr[$m[1]] = $flexFormData['settings']['flexform'][$m[1]];
						} elseif ( preg_match("/^settings\.(.*[^\.])$/i",$fieldKey,$m) ) {
							$cObjAr[$m[1]] = $flexFormData['settings'][$m[1]];
						} else $cObjAr[$fieldKey] = $flexFormData[$fieldKey];
					}
				}
			}
		}
		
		// initialize TSFE
		$cObj = \KERN23\ContentDesigner\Utility\TypoScript::cObjInit();
		
		// TypoScript FIELD Startpoint set
		$cObj->start(array_merge($row, $cObjAr));
		
		// Render preview with TypoScript
		$itemContent = \KERN23\ContentDesigner\Utility\TypoScript::parseTypoScriptObj($objType, $objArray, $cObj);
		
		// Reset
		$cObj->start($data,'tt_content'); // Reset des CURRENT Wert damit die Content ID wieder eingefuegt werden kann
	}
}

?>