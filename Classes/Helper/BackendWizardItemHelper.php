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
class BackendWizardItemHelper {

    /**
     * @var null|KERN23\ContentDesigner\Helper\IconRegistryHelper
     */
    private static $iconRegistry = null;

    /**
     * @var string
     */
    private static $prefix = 'content_designer';

    /**
     * @var string
     */
    private static $sheetName = 'cd';

    /**
     * Creates the new content element wizard Sheet
     *
     * @return void
     */
    public static function setWizardSheet() {
        $_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::$prefix]);

        // If TSconfig Wizard adding enabled, create the Sheet
        $label = ( trim($_extConfig['sheetTitle']) == '' ) ? 'LLL:EXT:content_designer/Resources/Private/Language/locallang_be.xlf:wizard.sheetTitle' : $_extConfig['sheetTitle'];

        // Generate tsConfig
        ExtensionManagementUtility::addPageTsConfig('
            mod.wizards.newContentElement.wizardItems.' . self::$sheetName . '.header = ' . $label . '
        ');
    }

    /**
     * Add as content_designer item to the element wizard
     *
     * @param $newElementKey
     * @param $newElementConfig
     * @return void
     */
    public static function addItemToWizard(&$newElementKey, &$newElementConfig) {
        // Get the icon if its an file register new icon
        if ( strlen($newElementConfig['icon']) > 0 ) {
            $icon = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($newElementConfig['icon']);

            if ( file_exists($icon) ) {
                if (self::$iconRegistry == NULL) self::$iconRegistry = GeneralUtility::makeInstance(\KERN23\ContentDesigner\Helper\IconRegistryHelper::class);
                self::$iconRegistry->registerNewIcon($newElementKey . '-icon', $icon);
                $newElementConfig['icon'] = $newElementKey . '-icon';
            }
        } else $newElementConfig['icon'] = 'contentdesigner-default';

        // Generate the tsconfig
        ExtensionManagementUtility::addPageTsConfig('
            mod.wizards.newContentElement.wizardItems.' . self::$sheetName . '.show := addToList(' . $newElementKey . ')
            mod.wizards.newContentElement.wizardItems.' . self::$sheetName . '.elements {
                ' . $newElementKey . ' {
                    iconIdentifier = ' . $newElementConfig['icon'] . '
                    title          = ' . $newElementConfig['title'] . '
                    description    = ' . $newElementConfig['description'] . '
                    tt_content_defValues.CType = ' . $newElementKey . '
                }
            }
		');
    }
}

?>