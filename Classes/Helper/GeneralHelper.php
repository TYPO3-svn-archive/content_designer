<?php

namespace KERN23\ContentDesigner\Helper;

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
 * General Helper Class
 *
 * @author	Hendrik Reimers <kontakt@kern23.de>
 * @package	ContentDesigner
 * @subpackage	tx_contentdesigner
 */
class GeneralHelper {

    /**
     * Helper function for translations
     *
     * @param $string
     * @return mixed
     * @return void
     */
    public static function translate($string) {
        if ( preg_match("/^LLL:(.*)$/",$string) && ($GLOBALS['LANG']) ) {
            return $GLOBALS['LANG']->sL($string);
        } else return $string;
    }

    /**
     * Checks if the current module is accepted to perform the magic with typoscript
     * For example the installTool doesn't like it.
     *
     * @return bool
     */
    public static function isModuleAcceptable() {
        // don't run in install tool
        $installToolVar = @GeneralUtility::_GP('install');
        if ( is_array($installToolVar) && ($installToolVar['extensionCompatibilityTester']['forceCheck'] == 1) ) return FALSE;

        // Not available in Permission Module
        #if ( @\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('M') == 'web_perm' ) return FALSE;
    }
}

?>