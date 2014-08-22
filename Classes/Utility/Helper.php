<?php

namespace KERN23\ContentDesigner\Utility;

class Helper {

    /**
	 * Parsed TypoScript Objekte
	 *
	 * @param string $string
	 * @return string
	 */
	public static function translate($string) {
		if ( preg_match("/^LLL:(.*)$/",$string) && ($GLOBALS['LANG']) ) {
			return $GLOBALS['LANG']->sL($string);
		} else return $string;
	}
	
	/**
	 * Checks compability
	 *
	 * @return boolean
	 */
	public static function compabilityCheck() {
		// Not available in Permission Module
		if ( @\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('M') == 'web_perm' ) return FALSE;
		
		// ...and Compability Tester (it crashes because TS isn't available)
		$installToolVar = @\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('install');
		if ( is_array($installToolVar) && ($installToolVar['extensionCompatibilityTester']['forceCheck'] == 1) ) return FALSE;
		
		// fix TCA problem in 6.0
		if (version_compare(TYPO3_branch, '6.1', '<')) {
			@\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA($table);
		}
		
		return true;
	}
	
	/**
	 * Returns some basic values for the Form selection Xclass hook
	 *
	 * @return array
	 */
	public static function getFormSettings() {
		// Get config
		$fCfg     = $GLOBALS['TCA']['tt_content']['columns']['CType']['config'];
		
		// Switch authMode
		switch ( (string)$fCfg['authMode'] ) {
			case 'explicitAllow':
				$iMode = 'ALLOW';
				break;
			case 'explicitDeny':
				$iMode = 'DENY';
				break;
			case 'individual':
				if ($iVal[4] === 'EXPL_ALLOW') {
					$iMode = 'ALLOW';
				} elseif ($iVal[4] === 'EXPL_DENY') $iMode = 'DENY';
				break;
		}
		
		// Icons:
		$icons = array(
			'ALLOW' => 'status-status-permission-granted',
			'DENY'  => 'status-status-permission-denied'
		);
							
		// Initialize labels
		$adLabel = array(
			'ALLOW' => $GLOBALS['LANG']->sl('LLL:EXT:lang/locallang_core.xlf:labels.allow'),
			'DENY' => $GLOBALS['LANG']->sl('LLL:EXT:lang/locallang_core.xlf:labels.deny')
		);
		
		return array($iMode, $icons, $adLabel);
	}
}

?>