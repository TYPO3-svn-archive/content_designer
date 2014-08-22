<?php

namespace KERN23\ContentDesigner\Utility;

class TypoScript {
	
	/**
	 * Returns the elements from anywhere ignoring the page ID
	 *
	 * @return array
	 */
	public static function getFromAnywhere($prefixId = 'tx_contentdesigner_', $firstTsLevel = 'tt_content.') {
		$cm      = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager');
		$tsSetup = $cm->getTypoScriptSetup();
		
		foreach ( array_keys($tsSetup[$firstTsLevel]) as $key ) {
			if ( preg_match('/^'.$prefixId.'(.*)\.$/i',$key,$match) ) {
				$retAr[$prefixId.$match[1]] = $tsSetup[$firstTsLevel][$key];
			}
		}
		
		return $retAr;
	}
	
	/**
     * Lädt die Typoscript konfiguration für das plugin / extension
     *
     * @param array $config
	 * @param string $prefixId
	 * @param integer $pageUid
	 * @param string $firstTsLevel
	 * @param boolean $noPageUidSubmit
     * @return array
     */
	public static function loadConfig(&$config, $prefixId, $pageUid = 0, $firstTsLevel = 'tt_content.', $noPageUidSubmit = FALSE) {
		$arr_list = self::loadTS($config, $pageUid, $noPageUidSubmit);
		if ( !is_array($arr_list) || (sizeof($arr_list) <= 0) || !is_array($arr_list[$firstTsLevel]) ) return $config;
		
		foreach ( array_keys($arr_list[$firstTsLevel]) as $key ) {
			if ( preg_match("/^".$prefixId."_(.*)\.$/i",$key,$match) ) {
				$retAr[$key] = $arr_list[$firstTsLevel][$key];
			}
		}
		
		return $retAr;
	}

	/**
     * Load the TypoScript Conf Array in the Backend
     *
     * @param array $conf
	 * @param integer $pageUid
	 * @param boolean $noPageUidSubmit
     * @return array
     */
	public static function loadTS(&$conf, $pageUid = 0, $noPageUidSubmit = FALSE) {
		$pid = ( $noPageUidSubmit == FALSE ) ? self::getPid() : self::getPid($pageUid); // Fixed bug, if page properties the pid must be determined not by given pageUid
		
		$ps       = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
		$rootline = $ps->getRootLine($pid);
		if (empty($rootline)) return FALSE;
		unset($ps);
		
		$tsObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\\TemplateService');
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
	public static function cObjInit() {
		$pid = self::getPid();
		
		$GLOBALS['TSFE'] = new \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController($TYPO3_CONF_VARS, $pid, 0, true);
		
		if (!is_object($GLOBALS['TT'])) {
			$GLOBALS['TT'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TimeTracker\\TimeTracker');
			$GLOBALS['TT']->start();
		}
		
		$GLOBALS['TSFE']->tmpl = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\\TemplateService');
		$GLOBALS['TSFE']->tmpl->tt_track = 0; // Do not log time-performance information
		
		$GLOBALS['TSFE']->sys_page = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
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
		
		return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
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
			return $cObj->cObjGetSingle($objType,$objArray);
		} else return FALSE;
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
			$tmp = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('edit');
			$pid = key($tmp['pages']);
		}
		if ( empty($pid) && ($_GET['returnUrl']) )              $pid = intval(preg_replace("/^.*id=([0-9]{1,}).*$/i","$1",$_GET['returnUrl'],1));
		if ( empty($pid) && isset($_GET['id']) )                $pid = intval($_GET['id']);
		if ( empty($pid) && isset($_GET['edit']) && is_array($_GET['edit']) && empty($_GET['edit']['pages']) ) {
			$table = key(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('edit'));
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