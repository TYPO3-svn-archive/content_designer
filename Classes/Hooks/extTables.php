<?php

namespace KERN23\ContentDesigner\Hooks;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \KERN23\ContentDesigner\Service\TypoScript;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \KERN23\ContentDesigner\Helper\BackendCTypeItemHelper;
use \KERN23\ContentDesigner\Helper\BackendWizardItemHelper;
use \KERN23\ContentDesigner\Helper\GeneralHelper;

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
 * Helper functions to extend the TCA
 *
 * @author	Hendrik Reimers <kontakt@kern23.de>
 * @package	ContentDesigner
 * @subpackage	tx_contentdesigner
 */
class ExtTables {

    const CD_PREFIX = 'tx_contentdesigner';

    /**
     * @var null|KERN23\ContentDesigner\Helper\IconRegistry
     */
    private static $iconRegistry = null;

    /**
     * Renders the TSconfig to register new content elements
     *
     * @return void
     */
    public static function registerNewContentElements() {
        // Load TS Setup Content Designer Items
        $table = 'tt_content';
        $items = TypoScript::loadConfig($config, self::CD_PREFIX, 0, $table . '.');

// @todo: List label override hook, maybe now a signal slot event then a hook?
//        $_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['content_designer']);
//        if ( $_extConfig['altLabelField'] == 1 ) $GLOBALS['TCA'][$table]['ctrl']['label_userFunc'] = 'EXT:content_designer/Classes/Hooks/Label.php:KERN23\\ContentDesigner\\Hooks\\Label->getUserLabel';
//        unset($_extConfig);

        // Render possible elements
        unset($items['___extendCType']);
        if ( count($items) ) {
            if ( self::$iconRegistry == NULL ) {
                self::$iconRegistry = GeneralUtility::makeInstance(\KERN23\ContentDesigner\Helper\IconRegistryHelper::class);
                self::$iconRegistry->registerDefaultIcon();
            }

            BackendWizardItemHelper::setWizardSheet();
            self::renderContentElements($items, $table);
        }
    }

    /**
     * Extends the ttContent CType with the Content Designer Flexform
     *
     * @return void
     */
    public static function extendTtContentTca() {
        // Load TypoScript
        $cdModConf = TypoScript::loadConfig($config, self::CD_PREFIX, 0);
        if ( !is_array($cdModConf['___extendCType']) ) return;
        $tsConf = &$cdModConf['___extendCType'];

        if ( !is_array($tsConf) ) return;

        // Prepare the CTypes to extend
        foreach ( $tsConf as $CType => $conf ) {
            $CType    = substr($CType, 0, strlen($CType) - 1);
            $tca      = ( !empty($conf['tca']) ) ? $conf['tca'] : '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.extended,tx_contentdesigner_flexform';
            $position = ( !empty($conf['tcaPosition']) ) ? $conf['tcaPosition'] : '';

            ExtensionManagementUtility::addToAllTCAtypes('tt_content', $tca, $CType, $position);

            // Set up default renderMethod (flexForm)
            if ($cdItem['cObjectFlexFile']) {
                // Use a FlexForm File
                $GLOBALS['TCA']['tt_content']['columns'][self::CD_PREFIX . '_flexform']['config']['ds']['default'] = 'FILE:' . $cdItem['cObjectFlexFile'];
            } else {
                // Use base XML structure (the rest comes with TypoScript)
                $GLOBALS['TCA']['tt_content']['columns'][self::CD_PREFIX . '_flexform']['config']['ds']['default'] = 'FILE:EXT:content_designer/Configuration/FlexForms/defaultPages.xml';
            }
        }
    }

    /**
     * Extends the pages configuration the magic to modify the fields are comming in the Hooks (FlexFormDs)
     *
     * @return void
     */
    public static function extendPagesTca() {
        // Get current page id
        $table   = 'pages';
        $pageUid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('edit');
        $pageUid = @key($pageUid[$table]);

        // Load TS Setup for content designer pages
        $pagesConfig = TypoScript::loadConfig($config, self::CD_PREFIX, $pageUid, $table . '.', TRUE);

        // do we have something to add?
        if( is_array($pagesConfig) && count($pagesConfig[self::CD_PREFIX . '_flexform']['settings.']['cObject.']) ) {
            $cdItem = &$pagesConfig[self::CD_PREFIX . '_flexform']['settings.'];

            ExtensionManagementUtility::addToAllTCAtypes('pages', $cdItem['tca']);

            if ($cdItem['cObjectFlexFile']) {
                // Use a FlexForm File
                $GLOBALS['TCA']['pages']['columns'][self::CD_PREFIX . '_flexform']['config']['ds']['default'] = 'FILE:' . $cdItem['cObjectFlexFile'];
            } else {
                // Use base XML structure (the rest comes with TypoScript)
                $GLOBALS['TCA']['pages']['columns'][self::CD_PREFIX . '_flexform']['config']['ds']['default'] = 'FILE:EXT:content_designer/Configuration/FlexForms/defaultPages.xml';
            }
        }
    }



    /* ************************************************************************************************************** */



    /**
     * Renders the content Elements for the Backend
     *
     * @param array $items
     * @param string $table
     */
    private static function renderContentElements(&$items, &$table) {
        foreach ( $items as $itemKey => $itemConfig ) {
            BackendWizardItemHelper::addItemToWizard($itemKey, $itemConfig['settings.']);
            BackendCTypeItemHelper::addItemToCType($itemKey, $itemConfig['settings.'], $table);
            self::addPlugin($itemKey, $itemConfig['settings.']);
        }
    }

    /**
     * Registers the content element as plugin
     *
     * @param $newElementKey
     * @param $newElementConfig
     * @return void
     */
    private static function addPlugin($newElementKey, $newElementConfig) {
        if ( strlen($newElementConfig['iconSmall']) > 0 ) {
            if ( file_exists($newElementConfig['iconSmall']) ) {
                self::$iconRegistry->registerNewIcon($newElementKey . '-iconSmall', $newElementConfig['iconSmall']);
                $newElementConfig['iconSmall'] = $newElementKey . '-iconSmall';
            }
        } else $newElementConfig['iconSmall'] = 'contentdesigner-defaultSmall';

        ExtensionManagementUtility::addPlugin(
            array(
                GeneralHelper::translate($newElementConfig['title']),
                $newElementKey,
                $newElementConfig['iconSmall']
            ),
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );
    }
}

?>