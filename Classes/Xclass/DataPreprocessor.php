<?php

namespace KERN23\ContentDesigner\Xclass;

// NEED TO HOOK IN FORMENGINE TOO

/**
 * Class/Function which manipulates the labels in the list and linkwizard mod
 *
 */
class DataPreprocessor extends \TYPO3\CMS\Backend\Form\DataPreprocessor {
	
	public function selectAddSpecial($dataAcc, $elements, $specialKey) {
		if ( $specialKey == 'explicitValues' ) {
			$explicitValues = parent::selectAddSpecial($dataAcc, $elements, $specialKey);
			
			$cm = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager');
			$tsSetup = $cm->getTypoScriptSetup();
			#die(\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(rawurldecode($explicitValues[3])));
			//tt_content:CType:textpic:ALLOW|[Erlauben] Text und Bilder
			foreach ( $tsSetup['tt_content.'] as $key => $val ) {
				if ( preg_match('/^tx_contentdesigner_(.*)\.$/', $key, $m) ) {
					$title = \KERN23\ContentDesigner\Utility\Helper::translate($val['settings.']['title']);
					$explicitValues[] = rawurlencode('tt_content:CType:tx_contentdesigner_'.$m[1].':ALLOW|[Erlauben] '.$title);
				}
			}
			#die(\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($explicitValues));
			return $explicitValues;
		} else return parent::selectAddSpecial($dataAcc, $elements, $specialKey);
	}
	
}

?>