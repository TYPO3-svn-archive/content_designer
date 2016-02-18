<?php

namespace KERN23\ContentDesigner\Hooks\Form;

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
 * !!! THIS IS THE MAIN MAGIC !!!
 *
 * Hook to modify the FlexForms Datastructure with the config by typoscript
 *
 * @author	Hendrik Reimers <kontakt@kern23.de>
 * @package	ContentDesigner
 * @subpackage	tx_contentdesigner
 */
class FlexFormDs {

    /**
     * @var string
     */
    private $prefix = 'tx_contentdesigner_';

    /**
     * The main hook function called by core function getFlexFormDS()
     *
     * @param $dataStructArray
     * @param $conf
     * @param $row
     * @param $table
     * @param $fieldName
     */
    public function getFlexFormDS_postProcessDS(&$dataStructArray, &$conf, &$row, &$table, &$fieldName) {
        $curObjType = $this->getElementTypeAndTable($row);

        // Get the typoscript configuration object
        $typoScript = TypoScript::loadConfig($conf, substr($this->prefix, 0, strlen($this->prefix) - 1), $row['pid'], $curObjType['table'] . '.');
        
        // Check whether to extend other CType's and identify the current element
        if ( !is_array($typoScript['___extendCType'][$row['CType'] . '.']) && !($curObjType) )
            return FALSE;

        // If a flexFile defined or copied by a plugin, nothing is to do
        $tsObj       = &$typoScript[$this->prefix . $curObjType['CType']]['settings.'];
        $tsObjAltern = &$typoScript[$this->prefix . substr($curObjType['CType'], 0, strlen($curObjType['CType']) - 1)]['settings.']; # alternative access
        $tsObjCType  = &$typoScript['___extendCType'][$row['CType'] . '.'];

        if ( empty($tsObj['renderMethod']) || !isset($tsObj['renderMethod']) ) $tsObj['renderMethod'] = 'flexForm';
        if ( empty($tsObjAltern['renderMethod']) || !isset($tsObjAltern['renderMethod']) ) $tsObjAltern['renderMethod'] = 'flexForm';

        if ( ($tsObj['renderMethod'] != 'flexForm') && ($tsObjAltern['renderMethod'] != 'flexForm') ) {
            unset($typoScript);
            return false;
        }

        // Load the Field Configuration for the current selected Object
        if ( is_array($cObject = $tsObj['cObject.']) || is_array($cObject = $tsObjAltern['cObject.']) || is_array($cObject = &$tsObjCType) ) {
            unset($typoScript);
        } else return false;

        // Create the dataStructArray
        return $this->getDataStructArray($cObject, $dataStructArray);
    }



    /* ************************************************************************************************************** */


    /**
     * Identifies the current selected object type and table
     *
     * @param $row
     * @return array|bool
     */
    private function getElementTypeAndTable(&$row) {
        // identify the current content object type
        if ( !isset($row['doktype']) ) {
            if ( !preg_match('/^' . $this->prefix . '(.*)/', $row['CType'], $CType) ) return false;

            // Current selected TS Object
            $curSelObj = $CType[1];
            $table     = 'tt_content';
        } else {
            $curSelObj = 'flexform.';
            $table     = 'pages';
        }

        return array(
            'CType' => $curSelObj,
            'table' => $table
        );
    }

    /**
     * Creates the FlexForm data structure array
     *
     * @param array $cObject
     */
    private function getDataStructArray(&$cObject, &$dataStructArray) {
        // Reset the Datastructure
        if ( is_array($dataStructArray) ) unset($dataStructArray['sheets']);

        // Do nothing if not good configured
        if ( sizeof($cObject) <= 0 ) return false;

        // Render the typoscript to flexform
        foreach ( $cObject as $flexSheet => $flexSheetData ) {
            // Create the Sheet
            $flexSheet = substr($flexSheet,0,strlen($flexSheet)-1);

            if ( !is_array($dataStructArray) ) continue;

            if ( $flexSheetData['sheetTitle'] != '' )
                $dataStructArray['sheets'][$flexSheet]['ROOT']['TCEforms']['sheetTitle'] = $flexSheetData['sheetTitle'];

            // Select the element list
            $dataSheet = &$dataStructArray['sheets'][$flexSheet]['ROOT']['el'];

            // Render the Element Liste
            $dataSheet = $this->renderElementList($dataSheet,$flexSheetData);
        }
    }

    /**
     * Generate the field
     *
     * @param $dataSheet
     * @param $flexSheetData
     * @return mixed
     */
    private function renderElementList($dataSheet,$flexSheetData) {
        foreach ( $flexSheetData['el.'] as $flexKey => $flexData ) {
            $flexKey = substr($flexKey,0,strlen($flexKey)-1);
            $dataSheet['settings.flexform.'.$flexKey]['TCEforms'] = $this->createFlexArrayRecursive($flexData);
        }

        return $dataSheet;
    }

    /**
     * Makes it recuresive
     *
     * @param $flexData
     * @param array $return
     * @return array
     */
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