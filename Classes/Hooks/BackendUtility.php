<?php
namespace KERN23\ContentDesigner\Hooks;

/*
 * Extends the content Element rendering to dynamicly extend the content with dynamic flexforms
 */

class BackendUtility {
	function getFlexFormDS_postProcessDS(&$dataStructArray, &$conf, &$row, &$table, &$fieldName) {
		if ( !isset($row['doktype']) ) {
			if ( !preg_match('/^tx_contentdesigner_(.*)/',$row['CType'],$CType) ) return false;
			
			// Current selected TS Object
			$curSelObj = $CType[1].'.';
			$table     = 'tt_content.';
		} else {
			$curSelObj = 'flexform.';
			$table     = 'pages.';
		}
		
		
		
		// Init TypoScript Object
		$arr_list  = \KERN23\ContentDesigner\Utility\TypoScript::loadConfig($conf, 'tx_contentdesigner', $row['pid'], $table);
		
		// If flex file set nothing to do anymore
		if ( isset($arr_list['tx_contentdesigner_'.$curSelObj]['settings.']['cObjectFlexFile']) || isset($arr_list['tx_contentdesigner_'.$curSelObj]['settings.']['cObjectFromPlugin']) ) {
			unset($arr_list);
			return false;
		}
		
		if ( is_array($dataStructArray) ) {
			// Reset the Datastructure
			unset($dataStructArray['sheets']['sDEF']);
		}
		
		// Load the Field Configuration for the current selected Object
		if ( is_array($arr_list['tx_contentdesigner_'.$curSelObj]['settings.']['cObject.']) ) {
			$cObject = $arr_list['tx_contentdesigner_'.$curSelObj]['settings.']['cObject.'];
			unset($arr_list);
		} else return false;
		
		// Make the Flexform fields
		if ( sizeof($cObject) <= 0 ) return false;
		foreach ( $cObject as $flexSheet => $flexSheetData ) {
			// Create the Sheet
			$flexSheet = substr($flexSheet,0,strlen($flexSheet)-1);
			
			if ( !is_array($dataStructArray) ) continue;
			
			if ( $flexSheetData['sheetTitle'] != '' ) {
				$dataStructArray['sheets'][$flexSheet]['ROOT']['TCEforms']['sheetTitle'] = $flexSheetData['sheetTitle'];
			}
			
			// Select the element list
			$dataSheet = &$dataStructArray['sheets'][$flexSheet]['ROOT']['el'];
			
			// Render the Element Liste
			$dataSheet = $this->renderElementList($dataSheet,$flexSheetData);
		}
		
		return $dataStructArray;
	}
	
	private function renderElementList($dataSheet,$flexSheetData) {
		foreach ( $flexSheetData['el.'] as $flexKey => $flexData ) {
			$flexKey = substr($flexKey,0,strlen($flexKey)-1);
			$dataSheet['settings.flexform.'.$flexKey]['TCEforms'] = $this->createFlexArrayRecursive($flexData);
		}
		
		return $dataSheet;
	}
	
	private function createFlexArrayRecursive($flexData,$return = array()) {
		if ( sizeof($flexData) <= 0 ) return $return;
		
		foreach ( $flexData as $flexConfKey => $flexConfVal ) {
			if ( preg_match("/^(.*)\.$/",$flexConfKey,$m) ) {
				$return[$m[1]] = $this->createFlexArrayRecursive($flexData[$flexConfKey],$return[$m[1]]);
			} else $return[$flexConfKey] = $flexConfVal;
		}
		
		return $return;
	}
}

?>