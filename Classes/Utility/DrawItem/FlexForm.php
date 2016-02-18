<?php

namespace KERN23\ContentDesigner\Utility\DrawItem;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \KERN23\ContentDesigner\Service\TypoScript;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Hendrik Reimers <kontakt@kern23.de>
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
 * Backend preview item hook
 *
 * @author	Hendrik Reimers <kontakt@kern23.de>
 * @package	ContentDesigner
 * @subpackage	tx_contentdesigner
 */
class FlexForm {
    
    /**
     * Preview of a content element *_pi1.
     *
     * @param $tscType
     * @param $row
     * @param $headerContent
     * @param $itemContent
     * @return bool
     */
    public function getContentElementPreview($typoScript, &$row, &$headerContent, &$itemContent) {
        // Load the Field Configuration for the current selected Object
        if ( is_array($typoScript['settings.']['previewObj.']) ) {
            $objType    = $typoScript['settings.']['previewObj'];
            $objArray   = $typoScript['settings.']['previewObj.'];

            $conf = &$typoScript['settings.'];
        } else return false;

        // Load the flexform by the table field
        $ffh = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Service\FlexFormService::class); // Static call not axcepted in 6.1.1
        $flexFormData = $ffh->convertFlexFormContentToArray($row['pi_flexform']);

        // load the flexform data
        if ( empty($this->settings['renderMethod']) || !isset($this->settings['renderMethod']) ) $this->settings['renderMethod'] = 'flexForm';
        if ( $conf['renderMethod'] == 'flexFormFile' || $conf['renderMethod'] == 'flexFormByPlugin' ) {
            $cObjAr = $this->renderFlexFormFile($conf, $flexFormData);
        } elseif ( is_array($conf['cObject.']) && sizeof($conf['cObject.']) > 0 ) {
            $cObjAr = $this->renderFlexFormField($conf, $flexFormData);
        }

        // Merge the FlexForm data to the preview
        $this->parsePreview($cObjAr, $itemContent, $row, $objType, $objArray);
    }



    /* ************************************************************************************************************** */


    /**
     * Loads the data by the xml structure of the flexform string
     *
     * @param $conf
     * @param $flexFormData
     */
    private function renderFlexFormField(&$conf, &$flexFormData) {
        $cObjAr = array();

        foreach ( array_keys($conf['cObject.']) as $sheet ) {
            $sheet = substr($sheet, 0, strlen($sheet) - 1);

            foreach ( array_keys($conf['cObject.'][$sheet.'.']['el.']) as $fieldKey ) {
                $fieldKey = substr($fieldKey, 0, strlen($fieldKey) - 1);
                $cObjAr[$fieldKey] = $flexFormData['settings']['flexform'][$fieldKey];
            }
        }

        return $cObjAr;
    }

    /**
     * Gets the flexform data by a file structure or a plugin
     *
     * @param $conf
     * @param $flexFormData
     * @return array
     */
    private function renderFlexFormFile(&$conf, &$flexFormData) {
        $cObjAr = array();

        if ( strlen($conf['cObject.']['plugin']) > 0 ) $conf['cObject.']['file'] = &$conf['cObject.']['plugin'];

        // Load Flexfile
        $flexDefinition = GeneralUtility::xml2array(file_get_contents(GeneralUtility::getFileAbsFileName($conf['cObject.']['file'])));

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

        return $cObjAr;
    }

    /**
     * Renders the typoscript preview object with the data from the flexform
     *
     * @param $cObjAr
     * @param $itemContent
     * @param $row
     * @param $objType
     * @param $objArray
     */
    private function parsePreview(&$cObjAr, &$itemContent, &$row, &$objType, &$objArray) {
        // initialize TSFE
        $cObj = TypoScript::cObjInit();

        // TypoScript FIELD Startpoint set
        $cObj->start(array_merge($row, $cObjAr));

        // Render preview with TypoScript
        $itemContent = TypoScript::parseTypoScriptObj($objType, $objArray, $cObj);

        // Reset
        $cObj->start($data, 'tt_content'); // Reset des CURRENT Wert damit die Content ID wieder eingefuegt werden kann
    }
}

?>