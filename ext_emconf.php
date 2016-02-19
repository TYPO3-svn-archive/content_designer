<?php

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Content Designer',
	'description' => 'Easy way to create new Content Elements, or extend any CType and page properties. Just with TypoScript! Useful examples, and predefined Elements like Google Maps already included.',
	'category' => 'plugin',
	'version' => '3.0.0',
	'state' => 'beta',
	'uploadfolder' => true,
	'createDirs' => '',
	'clearcacheonload' => false,
	'author' => 'Hendrik Reimers (kern23.de)',
	'author_email' => 'kontakt@kern23.de',
	'author_company' => 'KERN23.de',
	'constraints' => 
	array (
		'depends' => array (
			'extbase' => '7.4.0-7.6.99',
			'fluid' => '7.4.0-7.6.99',
			'typo3' => '7.4.0-7.6.99',
		),
		'conflicts' => array (
		),
		'suggests' => array (
		),
	),
);

