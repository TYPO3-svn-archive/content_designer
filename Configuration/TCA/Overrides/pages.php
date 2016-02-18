<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$temporaryColumns = array (
    'tx_contentdesigner_flexform' => array(
        'label' => '',
        'exclude' => 1,
        'config' => array (
            'type' => 'flex',
            'ds_pointerField' => 'doktype',
            'ds' => array(
                'default' => 'FILE:EXT:content_designer/Configuration/FlexForms/defaultPages.xml'
            )
        )
    )
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages',
    $temporaryColumns
);

?>