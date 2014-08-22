<?php

namespace KERN23\ContentDesigner\Xclass;

/**
 * Class/Function which manipulates the labels in the list and linkwizard mod
 *
 */
class FormEngine extends \TYPO3\CMS\Backend\Form\FormEngine {
	
	public function addSelectOptionsToItemArray($items, $fieldValue, $TSconfig, $field) {
		if ( $fieldValue['config']['special'] == 'explicitValues' ) {
			$items = parent::addSelectOptionsToItemArray($items, $fieldValue, $TSconfig, $field);
			
			$cm = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager');
			$tsSetup = $cm->getTypoScriptSetup();
			
			//tt_content:CType:textpic:ALLOW|[Erlauben] Text und Bilder
			foreach ( $tsSetup['tt_content.'] as $key => $val ) {
				if ( preg_match('/^tx_contentdesigner_(.*)\.$/', $key, $m) ) {
					$title = \KERN23\ContentDesigner\Utility\Helper::translate($val['settings.']['title']);
					$items[] = array(
						'[Erlauben] '.$title,
						'tt_content:CType:tx_contentdesigner_'.$m[1].':ALLOW',
						'status-status-permission-granted'
					);
				}
			}
			
			return $items;
		} else return parent::addSelectOptionsToItemArray($items, $fieldValue, $TSconfig, $field);
	}
	
}

?>