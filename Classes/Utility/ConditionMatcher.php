<?php

namespace KERN23\ContentDesigner\Utility;

/*
 * Called by the tsconfig user function user_cdTSconfig to match the content element values
 */

class ConditionMatcher {
	
	/**
     * Checks the Condition and returns true or false
     *
	 * @param string $string
     * @return bool
     */
	public static function evaluateCondition($string) {
		if ( !$_GET['edit']['tt_content'] ) return;
		
		if ( strstr($string,">=") ) {
			list($key, $whereValue)  = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('>=', $string, FALSE, 2);
			list($table,$whereField) = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('|', $key, FALSE, 2);
			$whereValue = str_replace("|",",",$whereValue);
			
			$where = $whereField.' >= '.$whereValue;
		} elseif ( strstr($string,"<=") ) {
			list($key, $whereValue)  = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('<=', $string, FALSE, 2);
			list($table,$whereField) = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('|', $key, FALSE, 2);
			$whereValue = str_replace("|",",",$whereValue);
			
			$where = $whereField.' <= '.$whereValue;
		} elseif ( strstr($string,">") ) {
			list($key, $whereValue)  = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('>', $string, FALSE, 2);
			list($table,$whereField) = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('|', $key, FALSE, 2);
			$whereValue = str_replace("|",",",$whereValue);
			
			$where = $whereField.' > '.$whereValue;
		} elseif ( strstr($string,"<") ) {
			list($key, $whereValue)  = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('<', $string, FALSE, 2);
			list($table,$whereField) = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('|', $key, FALSE, 2);
			$whereValue = str_replace("|",",",$whereValue);
			
			$where = $whereField.' < '.$whereValue;
		} elseif ( strstr($string,"=") ) {
			list($key, $whereValue)  = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('=', $string, FALSE, 2);
			list($table,$whereField) = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('|', $key, FALSE, 2);
			$whereValue = str_replace("|",",",$whereValue);
			
			$where = $whereField.' IN ('.$whereValue.')';
		}
		
		list($uid) = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', key($_GET['edit']['tt_content']), TRUE, 1);
		
		$res = @$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', $table, 'uid = '.$uid.' AND '.$where);
		
		if ( ($res) && (@$GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) ) {
			return true;
		} else return false;
	}
}

?>