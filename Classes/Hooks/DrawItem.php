<?php

namespace KERN23\ContentDesigner\Hooks;

use \KERN23\ContentDesigner\Service\TypoScript;
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
 * Backend preview item hook
 *
 * @author	Hendrik Reimers <kontakt@kern23.de>
 * @package	ContentDesigner
 * @subpackage	tx_contentdesigner
 */
class DrawItem implements \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface {

    private $cObj;

    /**
     * @var array
     */
    protected $typoScript;

    /**
     * @var string
     */
    private $prefix = 'tx_contentdesigner_';

    /**
     * Preprocesses the preview rendering of a content element.
     *
     * @param	tx_cms_layout	$parentObject:  Calling parent object
     * @param	boolean         $drawItem:      Whether to draw the item using the default functionalities
     * @param	string	        $headerContent: Header content
     * @param	string	        $itemContent:   Item content
     * @param	array		$row:           Record row of tt_content
     * @return	void
     */
    public function preProcess(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
        // If Content Designer Element render the preview
        $this->renderPreview($parentObject, $drawItem, $headerContent, $itemContent, $row);
    }

    /**
     * Modifies the Element to disable Dragging for cols
     *
     * @param	tx_cms_layout	$parentObject:  Calling parent object
     * @param	boolean         $drawItem:      Whether to draw the item using the default functionalities
     * @param	string	        $headerContent: Header content
     * @param	string	        $itemContent:   Item content
     * @param	array			$row:           Record row of tt_content
     * @return	void
     */
    private function renderPreview(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
        // Preview of content_designer elements
        $expr = '/^' . $this->prefix . '(.*)$/si';
        if ( preg_match($expr, $row['CType'], $match) ) {
            // Load the TypoScript Config
            $config           = array('row' => &$row);
            $typoScript       = TypoScript::loadConfig($config, substr($this->prefix, 0, strlen($this->prefix) - 1), $row['pid']);
            $this->typoScript = $typoScript[$row['CType']];
            unset($typoScript, $config);

            // Render the preview with default labels etc.? (default is now off)
            $drawItem = ( $this->typoScript['settings.']['enableDefaultDrawItem'] == 1 ) ? TRUE : FALSE;

            // Render the preview
            if ( empty($this->settings['renderMethod']) || !isset($this->settings['renderMethod']) ) $this->settings['renderMethod'] = 'flexForm';
            if ( $this->typoScript['settings.']['renderMethod'] == 'tca' ) {
                $previewRenderer = GeneralUtility::makeInstance(\KERN23\ContentDesigner\Utility\DrawItem\Tca::class);
            } else {
                $previewRenderer = GeneralUtility::makeInstance(\KERN23\ContentDesigner\Utility\DrawItem\FlexForm::class);
            }

            $previewRenderer->getContentElementPreview($this->typoScript, $row, $headerContent, $itemContent);

            // Link the preview content
            $itemContent = $parentObject->linkEditContent($itemContent, $row);
        }
    }
}

?>