<?php
namespace KERN23\ContentDesigner\Hooks;
use \TYPO3\CMS\Core\Utility\GeneralUtility as GeneralUtility;

/**
 * Class/Function to extend the typoscript Data Object (fields/data) to access them
 *
 */
class ContentRendererObject implements \TYPO3\CMS\Frontend\ContentObject\ContentObjectPostInitHookInterface {

    /**
     * Hook to extend the Data Array of the Object with the FlexForm fields
     *
     * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $pObject
     * @return bool
     */
    public function postProcessContentObjectInitialization(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer &$pObject) {
        if ( isset($pObject->data['tx_contentdesigner_flexform']) ) {
            $objData = $pObject->data['tx_contentdesigner_flexform'];
        } elseif ( isset($GLOBALS['TSFE']->page['tx_contentdesigner_flexform']) ) {
            $objData = $GLOBALS['TSFE']->page['tx_contentdesigner_flexform'];
        } else return false;

        if ( empty($objData) ) return false;

        $ffh       = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\FlexFormService');
        $flexArray = $ffh->convertFlexFormContentToArray($objData);

        if ( is_array($flexArray['settings']['flexform']) ) {
            if ( $pObject->getCurrentTable() == 'pages' ) $this->mergeData($pObject, $flexArray['settings']['flexform']);
            if ( $pObject->getCurrentTable() == 'tt_content' ) $this->mergeData($pObject, $flexArray['settings']['flexform']);

            unset($flexArray['settings']['flexform']);
        }

        if ( is_array($flexArray['settings']) ) {
            if ( $pObject->getCurrentTable() == 'pages' ) $this->mergeData($pObject, $flexArray['settings']);
            if ( $pObject->getCurrentTable() == 'tt_content' ) $this->mergeData($pObject, $flexArray['settings']);

            unset($flexArray['settings']);
        }

        if ( sizeof($flexArray) > 0 ) {
            if ( $pObject->getCurrentTable() == 'pages' ) $this->mergeData($pObject, $flexArray);
            if ( $pObject->getCurrentTable() == 'tt_content' ) $this->mergeData($pObject, $flexArray);

            unset($flexArray);
        }
    }

    /**
     * Merge function
     *
     * @param \TYPO3\CMS\Frontend\ContentObject\ContentObject $pObject
     * @param array $flexArray
     */
    private function mergeData(&$pObject, &$flexArray) {
        $pObject->data = array_merge($pObject->data, $flexArray);
        $GLOBALS['TSFE']->page = array_merge($GLOBALS['TSFE']->page, $flexArray);
    }
}

?>