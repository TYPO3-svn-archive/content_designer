<?php

namespace KERN23\ContentDesigner\Hooks;

/**
 * Class/Function which manipulates the labels in the list and linkwizard mod
 *
 */
class Label {
	
	public function getUserLabel(&$params, &$pObj) {
		$expr  = '/^tx_contentdesigner_(.*)$/si';
		$table = &$params['table'];
		$row   = &$params['row'];
		$title = &$params['title'];
		
		if ( ($table == 'tt_content') && preg_match($expr, $row['CType'], $match) ) {
			$altLabelField = $this->getAltLabel($row);
			
			if ( (!empty($altLabelField['label'])) || (!empty($altLabelField['field'])) || (!empty($altLabelField['userFunc'])) ) {
				$data  = $this->getFlexData($table, $row);
				
				if ( !empty($altLabelField['userFunc']) ) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($altLabelField['userFunc'], $params, $data);
				} elseif ( !empty($altLabelField['field']) ) {
					$title = $data['settings']['flexform'][$altLabelField['field']];
				} else {
					$title = $altLabelField['label'];
				}
				
				unset($data);
			} else {
				$title = $this->getDefaultLabel($table, $row);
			}
		} else {
			$title = $this->getDefaultLabel($table, $row);
		}
	}
	
	private function getDefaultLabel(&$table, &$row) {
		$tmp = $GLOBALS['TCA'][$table]['ctrl']['label_userFunc'];
		unset($GLOBALS['TCA'][$table]['ctrl']['label_userFunc']);
		
		$title = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordTitle($table, $row);
		
		$GLOBALS['TCA'][$table]['ctrl']['label_userFunc'] = $tmp;
		
		return $title;
	}
	
	private function getFlexData(&$table, &$row) {
		$ff   = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord($table,$row['uid'],'pi_flexform');
		$ffh  = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\FlexFormService');
		
		$data = $ffh->convertFlexFormContentToArray($ff['pi_flexform']);
		
		unset($ff, $ffh);
		
		return $data;
	}
	
	private function getAltLabel(&$row) {
		if ( empty($row['pid']) ) $row['pid'] = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('expandPage');
		
		$config        = array('row' => &$row);
		$arr_list      = \KERN23\ContentDesigner\Utility\TypoScript::loadConfig($config, 'tx_contentdesigner', $row['pid'], 'tt_content.', true);
		$tsSetup       = $arr_list[$row['CType'].'.'];
		unset($config, $arr_list);
		
		return array(
				"label"    => $tsSetup['settings.']['altLabelField'],
				"field"    => $tsSetup['settings.']['altLabelField.']['field'],
				"userFunc" => $tsSetup['settings.']['altLabelField.']['userFunc']
			    );
	}
	
}

?>