<?php

namespace KERN23\ContentDesigner\Xclass;

/**
 * Hook to add the content designer elements to the explicit_allowdeny selection of be_groups
 *
 */
class FormEngine extends \TYPO3\CMS\Backend\Form\FormEngine {
	
	public function addSelectOptionsToItemArray($items, $fieldValue, $TSconfig, $field) {
		$items = parent::addSelectOptionsToItemArray($items, $fieldValue, $TSconfig, $field);
		
		if ( (key($_GET['edit']) == 'be_groups') && ($fieldValue['config']['special'] == 'explicitValues') ) {
			
			// Get some basic values
			list($iMode, $icons, $adLabel) = \KERN23\ContentDesigner\Utility\Helper::getFormSettings();
			
			// Adding a div / Header
			$items[] = array(
				'Content Designer:',
				'--div--'
			);
			
			// Get TypoScript
			$cdElements = \KERN23\ContentDesigner\Utility\TypoScript::getFromAnywhere();
			
			//tt_content:CType:textpic:ALLOW|[Erlauben] Text und Bilder
			foreach ( $cdElements as $key => $val ) {
				$title = \KERN23\ContentDesigner\Utility\Helper::translate($val['settings.']['title']);
				
				$items[] = array(
					'['.$adLabel[$iMode].'] '.$title,
					'tt_content:CType:'.$key.':'.$iMode,
					$icons[$iMode]
				);
			}
		}
		
		return $items;
	}
	
}

?>