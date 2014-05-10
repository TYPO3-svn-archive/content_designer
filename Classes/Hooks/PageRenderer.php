<?php
namespace KERN23\ContentDesigner\Hooks;

/**
 * Class/Function which controls the backend for disable drag and drop
 *
 */
class PageRenderer {

	/**
	 * wrapper function called by hook (t3lib_pageRenderer->render-preProcess)
	 *
	 * @param	array	            $parameters: An array of available parameters
	 * @param	\TYPO3\CMS\Core\Page\PageRenderer $pageRenderer: The parent object that triggered this hook
	 * @return	void
	 */
	public function addJSCSS($parameters, &$pageRenderer) {
		if( $GLOBALS['MCONF']['name'] == 'web_layout' ) {
			$this->addJS($parameters, $pageRenderer);
			$this->addCSS($parameters, $pageRenderer);
		}
	}
	
	/**
	 * method that adds JS files within the page renderer
	 *
	 * @param	array	            $parameters: An array of available parameters while adding JS to the page renderer
	 * @param	\TYPO3\CMS\Core\Page\PageRenderer $pageRenderer: The parent object that triggered this hook
	 * @return	void
	 */
	protected function addJS($parameters, &$pageRenderer) {
		// add JavaScript library
		$pageRenderer->addJsFile(
			$GLOBALS['BACK_PATH'] . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('content_designer') . 'Resources/Public/Backend/JavaScript/dragDrop.js',
			$type = 'text/javascript',
			$compress = TRUE,
			$forceOnTop = FALSE,
			$allWrap = ''
		);
	}
	
	/**
	 * method that adds JS files within the page renderer
	 *
	 * @param	array	            $parameters: An array of available parameters while adding JS to the page renderer
	 * @param	\TYPO3\CMS\Core\Page\PageRenderer $pageRenderer: The parent object that triggered this hook
	 * @return	void
	 */
	protected function addCSS($parameters, &$pageRenderer) {
		$pageRenderer->addCssFile(
			$GLOBALS['BACK_PATH'] . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('content_designer') . 'Resources/Public/Backend/Css/dragDrop.css',
			'stylesheet',
			'screen'
		);
	}
}

?>
