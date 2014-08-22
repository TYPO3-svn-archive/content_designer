<?php

namespace KERN23\ContentDesigner\Xclass;

/**
 * Hook to add the content designer elements to the explicit_allowdeny selection of be_groups
 *
 */
class DataPreprocessor extends \TYPO3\CMS\Backend\Form\DataPreprocessor {
	
	public function selectAddSpecial($dataAcc, $elements, $specialKey) {
		if ( (key($_GET['edit']) == 'be_groups') && ($specialKey == 'explicitValues') ) {
			$explicitValues = parent::selectAddSpecial($dataAcc, $elements, $specialKey);
			
			// Get some basic values
			list($iMode, $icons, $adLabel) = \KERN23\ContentDesigner\Utility\Helper::getFormSettings();
			
			// Adding a div / Header
			$explicitValues[] = rawurlencode('tt_content:CType:--div--:'.$iMode);
			
			// Get TypoScript
			$cdElements = \KERN23\ContentDesigner\Utility\TypoScript::getFromAnywhere();
			
			//tt_content:CType:textpic:ALLOW|[Erlauben] Text und Bilder
			foreach ( $cdElements as $key => $val ) {
				$title            = \KERN23\ContentDesigner\Utility\Helper::translate($val['settings.']['title']);
				$explicitValueStr = 'tt_content:CType:'.$key.':'.$iMode;
				
				if ( array_search($explicitValueStr, $elements) ) $explicitValues[] = rawurlencode($explicitValueStr);
			}
			
			return $explicitValues;
		} else return parent::selectAddSpecial($dataAcc, $elements, $specialKey);
	}
	
}

?>