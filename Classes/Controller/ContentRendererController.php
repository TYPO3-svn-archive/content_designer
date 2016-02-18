<?php

namespace KERN23\ContentDesigner\Controller;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Hendrik Reimers <kontakt@kern23.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * ************************************************************* */

/**
 * Content Designer Controller
 *
 * @package ContentDesigner
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class ContentRendererController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

    /**
     * Shows a single item
     *
     * @todo integrate caching framework
     * @return void
     */
    public function showAction() {
        // Modifies the Render Object
        $this->cleanRenderObj($this->settings);

        // Load the data
        if ( empty($this->settings['renderMethod']) || !isset($this->settings['renderMethod']) ) $this->settings['renderMethod'] = 'flexForm';
        $cObjAr = &$this->settings['flexform'];

        // Append extra static fields
        if ( sizeof($this->settings['cObjectStaticData.']) > 0 ) {
            foreach ( $this->settings['cObjectStaticData.'] as $key => $val ) {
                $cObjAr[$key] = $val;
            }
        }

        // Content Object loading
        $this->cObj = $this->configurationManager->getContentObject(); // temp save of the original data

        // Load the content object data
        $data = $this->cObj->data;

        // Merge the CD Data with the current data object
        if ( is_array($cObjAr) ) {
            $this->cObj->start(array_merge($data, $cObjAr));
        } else $this->cObj->start($data);

        // Execute rendering by TypoScript
        $itemContent = \KERN23\ContentDesigner\Service\TypoScript::parseTypoScriptObj($this->settings['renderObj'], $this->settings['renderObj.'], $this->cObj);

        // Reset to default data object
        $this->cObj->start($data, 'tt_content'); // Reset the DATA with the original data

        // Return result
        return $itemContent;
    }

    /**
     * Normalize the Config Array
     *
     * @param array $settings
     * @return void
     */
    private function cleanRenderObj(&$settings) {
        $this->settings['renderObj.'] = $this->settings['renderObj'];
        $this->settings['renderObj'] = $this->settings['renderObj']['_typoScriptNodeValue'];
        unset($this->settings['renderObj.']['_typoScriptNodeValue']);

        $tsParser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\TypoScriptService');
        $this->settings['renderObj.'] = $tsParser->convertPlainArrayToTypoScriptArray($settings['renderObj.']);
    }
}

?>