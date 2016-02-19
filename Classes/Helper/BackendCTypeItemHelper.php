<?php

namespace KERN23\ContentDesigner\Helper;

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * Helper functions to add items to the content element wizard
 *
 * @author	Hendrik Reimers <kontakt@kern23.de>
 * @package	ContentDesigner
 * @subpackage	tx_contentdesigner
 */
class BackendCTypeItemHelper {

    /**
     * @var string
     */
    private static $prefix      = 'content_designer';

    /**
     * @var string
     */
    private static $prefixCType = 'tx_contentdesigner_';

    /**
     * @var bool
     */
    private static $dividerAdded = FALSE;

    /**
     * Adds a content_designer item to the CType Dropdown
     *
     * @param $newElementKey
     * @param $newElementConfig
     * @param $table
     * @return void
     */
    public static function addItemToCType(&$newElementKey, &$newElementConfig, &$table) {
        if ( self::$dividerAdded === FALSE ) {
            $_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::$prefix]);

            $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = array(
                $_extConfig['sheetTitle'],
                '--div--'
            );

            self::$dividerAdded = TRUE;
        }

        switch($newElementConfig['renderMethod']) {
            default:
                self::renderByFlexForm($newElementKey, $newElementConfig, $table);
                break;

            case 'flexFormFile':
                self::renderByFlexFormFile($newElementKey, $newElementConfig, $table);
                break;

            case 'flexFormByPlugin':
                self::renderByFlexFormOfPlugin($newElementKey, $newElementConfig, $table);
                break;
        }
    }



    /* ************************************************************************************************************** */



    /**
     * Renders an element defined by a flexform configuration array
     *
     * @param $newElementKey
     * @param $newElementConfig
     * @param $table
     * @return void;
     */
    private static function renderByFlexForm(&$newElementKey, &$newElementConfig, &$table) {
        self::loadDefaultTcaLayout($newElementKey, $newElementConfig);

        // Use base XML structure (the rest comes with TypoScript)
        $GLOBALS['TCA'][$table]['columns']['pi_flexform']['config']['ds'][','.$newElementKey] =
            'FILE:EXT:content_designer/Configuration/FlexForms/default.xml';
    }

    /**
     * Renders the element by a flexForm file
     *
     * @param $newElementKey
     * @param $newElementConfig
     * @param $table
     * @return void;
     */
    private static function renderByFlexFormFile(&$newElementKey, &$newElementConfig, &$table) {
        self::loadDefaultTcaLayout($newElementKey, $newElementConfig);

        $GLOBALS['TCA'][$table]['columns']['pi_flexform']['config']['ds'][','.$newElementKey] =
            'FILE:'.$newElementConfig['cObjectFlexFile'];
    }

    /**
     * Renders the element by a flexform getting from another plugin configuration
     *
     * @param $newElementKey
     * @param $newElementConfig
     * @param $table
     * @return void;
     */
    private static function renderByFlexFormOfPlugin(&$newElementKey, &$newElementConfig, &$table) {
        self::loadDefaultTcaLayout($newElementKey, $newElementConfig);

        $GLOBALS['TCA'][$table]['columns']['pi_flexform']['config']['ds'][','.$newElementKey] =
            $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds'][$newElementConfig['cObjectFromPlugin'].',list'];
    }



    /* ************************************************************************************************************** */



    /**
     * Sets the default TCA layout
     *
     * @param string $newElementKey
     * @param array $newElementConfig
     * @param string $table
     * @return void
     */
    private static function loadDefaultTcaLayout(&$newElementKey, &$newElementConfig, $table = 'tt_content') {
        // Set the default TCA fields by string or automaticly copy them by other code
        if ( !empty($newElementConfig['tca']) ) {
            $GLOBALS['TCA'][$table]['types'][$newElementKey]['showitem'] = $newElementConfig['tca'];
        } else {
            $type     = ( !empty($newElementConfig['tcaFromType']) )         ? $newElementConfig['tcaFromType'] : 'header';
            $position = ( !empty($newElementConfig['tcaFromTypePosition']) ) ? $newElementConfig['tcaFromTypePosition'] : 'after:header';

            $GLOBALS['TCA'][$table]['types'][$newElementKey]['showitem'] = $GLOBALS['TCA']['tt_content']['types'][$type]['showitem'];

            if ( !empty($newElementConfig['cObject.']) || !empty($newElementConfig['cObjectFlexFile']) || !empty($newElementConfig['cObjectFromPlugin']) )
                ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'pi_flexform', $newElementKey, $position);
        }
    }
}

?>