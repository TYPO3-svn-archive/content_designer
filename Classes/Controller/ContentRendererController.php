<?php

namespace KERN23\ContentDesigner\Controller;

    /* * *************************************************************
     *  Copyright notice
     *
     *  (c) 2013
     *  All rights reserved
     *
     *  This script is part of the TYPO3 project. The TYPO3 project is
     *  free software; you can redistribute it and/or modify
     *  it under the terms of the GNU General Public License as published by
     *  the Free Software Foundation; either version 3 of the License, or
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
     * ************************************************************* */

/**
 *
 *
 * @package nnhshpersonendaten
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class ContentRendererController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
    
  /**
   * Shows a single item
   *
   *
   * @return void
   */
  public function showAction() {
		// Modifies the Render Object
		$this->cleanRenderObj($this->settings);
		
		// Flex Data laden
		$cObjAr = $this->settings['flexform'];
		
		// Extra Felder laden
		if ( sizeof($this->settings['cObjectStaticData.']) > 0 ) {
			foreach ( $this->settings['cObjectStaticData.'] as $key => $val ) {
				$cObjAr[$key] = $val;
			}
		}
		
		// Content Object laden
		$this->cObj = $this->configurationManager->getContentObject(); // Die original Daten zwischen speichern
		
		// Content Data laden
		$data = $this->cObj->data;
		
		// Daten mergen
		if ( is_array($cObjAr) ) {
			$this->cObj->start(array_merge($data, $cObjAr));
		} else $this->cObj->start($data);
		
		// Ausfuehren
		$itemContent = \KERN23\ContentDesigner\Utility\TypoScript::parseTypoScriptObj($this->settings['renderObj'], $this->settings['renderObj.'], $this->cObj);
		
		// Zurücksetzen
		$this->cObj->start($data, 'tt_content'); // Reset des CURRENT Wert damit die Content ID wieder eingefuegt werden kann
		
		// Liefern
		return $itemContent;
  }
	
	/**
   * Normalize the Config Array
   *
   *
	 * @param array $settings
   * @return void
   */
	private function cleanRenderObj(&$settings) {
		$this->settings['renderObj.'] = $this->settings['renderObj'];
		$this->settings['renderObj'] = $this->settings['renderObj']['_typoScriptNodeValue'];
		unset($this->settings['renderObj.']['_typoScriptNodeValue']);
		
		$tsParser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\TypoScriptService');
		$this->settings['renderObj.'] = $tsParser->convertPlainArrayToTypoScriptArray($settings['renderObj.']);
	}
	
}