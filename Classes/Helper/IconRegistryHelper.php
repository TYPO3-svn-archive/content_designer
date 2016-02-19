<?php

namespace KERN23\ContentDesigner\Helper;

use \TYPO3\CMS\Core\Imaging\IconRegistry;
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
 * IconRegistry Helper Class
 *
 * @author	Hendrik Reimers <kontakt@kern23.de>
 * @package	ContentDesigner
 * @subpackage	tx_contentdesigner
 */
class IconRegistryHelper {

    /**
     * @var \TYPO3\CMS\Core\Imaging\IconRegistry
     */
    protected $iconRegistry;

    /**
     * @var string
     */
    private static $prefix = 'content_designer';

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct() {
        $this->iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    }

    /**
     * Registers the default icon
     * Could be an bitmap image (eq. jpg) or svg
     *
     * @return void
     */
    public function registerDefaultIcon() {
        $this->registerIcon('contentdesigner-default', ExtensionManagementUtility::extPath(self::$prefix) . 'ce_wiz.gif');
        $this->registerIcon('contentdesigner-defaultSmall', ExtensionManagementUtility::extPath(self::$prefix) . 'ext_icon.gif');
    }

    /**
     * Registers a new icon
     *
     * @param string $identifier
     * @param string $iconFile
     * @return void
     */
    public function registerNewIcon($identifier, $iconFile) {
        $this->registerIcon($identifier, $iconFile);
    }



    /* ************************************************************************************************************** */


    /**
     * Registers new icons
     *
     * @param $identifier
     * @param $iconFile
     */
    private function registerIcon($identifier, $iconFile) {
        $fileInfo = pathinfo($iconFile);

        if ( $fileInfo['extension'] == 'svg' ) {
            if ( !$this->iconRegistry->isRegistered($identifier) ) $this->iconRegistry->registerIcon(
                $identifier,
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                array('source' => $iconFile)
            );
        } else {
            if ( !$this->iconRegistry->isRegistered($identifier) ) $this->iconRegistry->registerIcon(
                $identifier,
                \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
                array('source' => $iconFile)
            );
        }
    }
}

?>