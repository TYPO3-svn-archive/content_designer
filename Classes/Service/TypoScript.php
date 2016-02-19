<?php

namespace KERN23\ContentDesigner\Service;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

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
 * Helper functions for getting the TypoScript Setup
 *
 * @author	Hendrik Reimers <kontakt@kern23.de>
 * @package	ContentDesigner
 * @subpackage	tx_contentdesigner
 */
class TypoScript {

    private static $cache = array();

    /**
     * Returns the elements from anywhere ignoring the page ID but the TypoScript must be in root line
     *
     * @param string $firstTsLevel
     * @param string $prefixId
     * @param bool $includeAdditionalNames
     * @return array
     */
    public static function getFromAnywhere($firstTsLevel = 'tt_content.', $prefixId = 'tx_contentdesigner_', $includeAdditionalNames = FALSE) {
        $objectManager        = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');
        $tsSetup              = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        // Find content designer elements
        foreach ( array_keys($tsSetup[$firstTsLevel]) as $key ) {
            if ( preg_match('/^'.$prefixId.'(.*)\.$/i', $key, $match) ) {
                $retAr[$prefixId . $match[1]] = $tsSetup[$firstTsLevel][$key];
            }
        }

        if ( ($includeAdditionalNames == TRUE) && is_array($tsSetup['module.']['tx_contentdesigner.']['manualExplicitAllowDeny.']) ) {
            foreach ( array_keys($tsSetup['module.']['tx_contentdesigner.']['manualExplicitAllowDeny.']) as $key ) {
                $retAr[$key]['settings.']['title'] = $tsSetup['module.']['tx_contentdesigner.']['manualExplicitAllowDeny.'][$key];
            }
        }

        return $retAr;
    }

    /**
     * Loads the typoscript configuration for the plugin / extension
     *
     * @param array $config
     * @param string $identifier
     * @param integer $pageUid
     * @param string $firstTsLevel
     * @param boolean $noPageUidSubmit
     * @return array
     */
    public static function loadConfig(&$config, $prefixId = 'tx_contentdesigner', $pageUid = 0, $firstTsLevel = 'tt_content.', $noPageUidSubmit = FALSE) {
        if ( isset(self::$cache[$pageUid]) ) return self::$cache[$pageUid];

        // Get the typoscript
        $arr_list = self::loadTS($config, $pageUid, $noPageUidSubmit);

        // nothing? return!
        if ( !is_array($arr_list) || (sizeof($arr_list) <= 0) ) return $config;

        // Append special for extending ctypes
        if ( is_array($arr_list['module.']['tx_contentdesigner.']['extendCType.']) )
            $retAr['___extendCType'] = $arr_list['module.']['tx_contentdesigner.']['extendCType.'];

        // is nothing more? then return
        if ( is_array($arr_list[$firstTsLevel]) )
            foreach ( array_keys($arr_list[$firstTsLevel]) as $key ) {
                if ( preg_match("/^".$prefixId."_(.*)\.$/i", $key, $match) )
                    $retAr[$prefixId . '_' . $match[1]] = $arr_list[$firstTsLevel][$key];
            }

        // if absolutely nothing then return
        if ( sizeof($retAr) <= 0 ) return $config;

        // Static caching
        self::$cache[$pageUid] = $retAr;

        // Return result
        return $retAr;
    }

    /**
     * Parsed TypoScript Objekte
     *
     * @param string $objType
     * @param array $objArray
     * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj
     */
    public static function parseTypoScriptObj($objType, $objArray, $cObj) {
        if ( (!empty($objType)) && (sizeof($objArray) > 0) ) {
            return $cObj->cObjGetSingle($objType, $objArray);
        } else return FALSE;
    }

    /**
     * Public function of cObject init
     *
     * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public function cObjInit() {
        return self::cObjectInit();
    }



    /* ************************************************************************************************************** */



    /**
     * Load the TypoScript Conf Array in the Backend
     *
     * @param array $conf
     * @param integer $pageUid
     * @param boolean $noPageUidSubmit
     * @return array
     */
    private static function loadTS(&$conf, $pageUid = 0, $noPageUidSubmit = FALSE) {
        $pid = ( $noPageUidSubmit == FALSE ) ? self::getPid() : self::getPid($pageUid); // Fixed bug, if page properties the pid must be determined not by given pageUid

        $ps       = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
        $rootline = $ps->getRootLine($pid);
        if (empty($rootline)) return FALSE;
        unset($ps);

        $tsObj = GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\TemplateService::class);
        $tsObj->tt_track = 0;
        $tsObj->init();
        $tsObj->runThroughTemplates($rootline);
        $tsObj->generateConfig();

        return $tsObj->setup;
    }

    /**
     * Inits the cObject for the Backend
     *
     * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    private static function cObjectInit() {
        $pid = self::getPid();

        $GLOBALS['TSFE'] = new TypoScriptFrontendController($TYPO3_CONF_VARS, $pid, 0, true);

        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = GeneralUtility::makeInstance(\TYPO3\CMS\Core\TimeTracker\TimeTracker::class);
            $GLOBALS['TT']->start();
        }

        $GLOBALS['TSFE']->tmpl = GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\TemplateService::class);
        $GLOBALS['TSFE']->tmpl->tt_track = 0; // Do not log time-performance information

        $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
        //$GLOBALS['TSFE']->sys_page->init($GLOBALS['TSFE']->showHiddenPage); // This makes problems if page is hidden!
        $GLOBALS['TSFE']->sys_page->init(true);

        // If the page is not found (if the page is a sysfolder, etc), then return no URL, preventing any further processing which would result in an error page.
        $page = $GLOBALS['TSFE']->sys_page->getPage($pid);
        if (count($page) == 0) return FALSE;
        if ($page['doktype'] == 4 && count($GLOBALS['TSFE']->getPageShortcut($page['shortcut'], $page['shortcut_mode'], $page['uid'])) == 0) return FALSE; // If the page is a shortcut, look up the page to which the shortcut references, and do the same check as above.
        // Removed the following line, to allow content on sysFolders
        //if ($page['doktype'] == 199 || $page['doktype'] == 254) return FALSE; // Spacer pages and sysfolders result in a page not found page too…

        $GLOBALS['TSFE']->tmpl->runThroughTemplates($GLOBALS['TSFE']->sys_page->getRootLine($pid), $template_uid);
        $GLOBALS['TSFE']->tmpl->generateConfig();
        $GLOBALS['TSFE']->tmpl->loaded = 1;
        $GLOBALS['TSFE']->getConfigArray();
        $GLOBALS['TSFE']->linkVars = ''.$GLOBALS['TSFE']->config['config']['linkVars'];

        return GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
    }

    /**
     * Varios ways to get the Page ID (most needed in BE)
     *
     * @param integer $pageUid
     */
    private static function getPid($pageUid = 0) {
        unset($pid);

        // Try to get the current page id to load the TS Setup from it
        if ( intval($pageUid) > 0 )                             $pid = intval($pageUid);
        //if ( empty($pid) && (intval($conf['row']['pid']) > 0) ) $pid = intval($conf['row']['pid']);
        if ( empty($pid) && isset($_GET['edit']) && is_array($_GET['edit']['pages']) ) {
            $tmp = GeneralUtility::_GP('edit');
            $pid = key($tmp['pages']);
        }
        if ( empty($pid) && ($_GET['returnUrl']) )              $pid = intval(preg_replace("/^.*id=([0-9]{1,}).*$/i","$1",$_GET['returnUrl'],1));
        if ( empty($pid) && isset($_GET['id']) )                $pid = intval($_GET['id']);
        if ( empty($pid) && isset($_GET['edit']) && is_array($_GET['edit']) && empty($_GET['edit']['pages']) ) {
            $table = key(GeneralUtility::_GP('edit'));
            $UIDs  = array_keys($_GET['edit'][$table]);
            $ce    = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord($table, intval($UIDs[0]), 'pid');

            $pid   = $ce['pid'];
        }
        if ( empty($pid) && ($_GET['CB']['paste']) ) $pid = intval(preg_replace("/^.*\|([0-9]{1,})$/i","$1",$_GET['CB']['paste'],1)); # Get the pid in exlicitAllow Mode on paste
        if ( empty($pid) && ($_GET['redirect']) )    $pid = intval(preg_replace("/^.*id=([0-9]{1,}).*$/i","$1",$_GET['redirect'],1)); # Get the pid in explicitAllow Mode on delete
        if ( empty($pid) ) return FALSE;

        return $pid;
    }
}

?>