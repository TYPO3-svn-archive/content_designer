<?php

namespace KERN23\ContentDesigner\Backend\Form\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Backend\Form\FormDataProvider;
use KERN23\ContentDesigner\Service\TypoScript;

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
 ***/

class AbstractItemProvider implements FormDataProviderInterface {

    /**
     * Injects the content designer explicit allow/deny configuration.
     *
     * @param array $result
     * @return array
     */
    public function addData(array $result) {
        // Only if editing the be_groups table
        if ( ($result['tableName'] != 'be_groups') && is_array($result['processedTca']['columns']['explicit_allowdeny']['config']['items']) ) return $result;

        // Preconf
        $table          = 'tt_content';
        $explicitADMode = ( $GLOBALS['TYPO3_CONF_VARS']['BE']['explicitADmode'] == 'explicitAllow' ) ? 'ALLOW' : 'DENY';

        $adModeLang     = array(
            'ALLOW' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:labels.allow'),
            'DENY'  => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:labels.deny')
        );
        $icons         = array(
            'ALLOW' => 'status-status-permission-granted',
            'DENY'  => 'status-status-permission-denied',
        );

        // Load TS Setup Content Designer Items
        $contentDesignerItems = TypoScript::getFromAnywhere($table . '.', 'tx_contentdesigner_', TRUE);

        // Merge the content designer items to the explicitAllowDeny selector
        if ( sizeof($contentDesignerItems) > 0 ) {
            $items = &$result['processedTca']['columns']['explicit_allowdeny']['config']['items'];

            // Add divider
            $items[] = array(
                'Content Designer:',
                '--div--',
                NULL,
                NULL
            );

            // Add items
            foreach ( $contentDesignerItems as $itemKey => $itemConf ) {
                $itemSettings = &$itemConf['settings.'];

                // Put into result
                $items[] = array(
                    '[' . $adModeLang[$explicitADMode] . '] ' . $itemSettings['title'],
                    $table . ':CType:' . $itemKey . ':' . $explicitADMode,
                    $icons[$explicitADMode],
                    NULL
                );
            }
        }

        return $result;
    }
}