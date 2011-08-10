<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
$TCA['tx_renveilledocumentaire_sources'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_sources',		
		'label'     => 'nom',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l10n_parent',	
		'transOrigDiffSourceField' => 'l10n_diffsource',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_renveilledocumentaire_sources.gif',
	),
);

$TCA['tx_renveilledocumentaire_keywords'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_keywords',		
		'label'     => 'word',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_renveilledocumentaire_keywords.gif',
	),
);

$TCA['tx_renveilledocumentaire_auteurs'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_auteurs',		
		'label'     => 'nom',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_renveilledocumentaire_auteurs.gif',
	),
);

$TCA['tx_renveilledocumentaire_veilles'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_veilles',		
		'label'     => 'titre',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_renveilledocumentaire_veilles.gif',
	),
);

$TCA['tx_renveilledocumentaire_notices'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_notices',		
		'label'     => 'titre',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_renveilledocumentaire_notices.gif',
	),
);

$TCA['tx_renveilledocumentaire_fichiers_stats'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_fichiers_stats',		
		'label'     => 'fichier',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_renveilledocumentaire_fichiers_stats.gif',
	),
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

$tempColumns = array (
	'ren_veilledocumentaire_notices' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_news.notices',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_renveilledocumentaire_notices',	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 50,
				'wizards' => array(
					'suggest' => array(
					'type' => 'suggest',
					),
				),
			)
		),
);
t3lib_div::loadTCA('tt_news');
t3lib_extMgm::addTCAcolumns('tt_news',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tt_news','ren_veilledocumentaire_notices');

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:ren_veille_documentaire/flexform_ds_pi1.xml');


if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_renveilledocumentaire_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_renveilledocumentaire_pi1_wizicon.php';
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tt_content.list_type_pi2',
	$_EXTKEY . '_pi2',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:ren_veille_documentaire/flexform_ds_pi2.xml');


if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_renveilledocumentaire_pi2_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi2/class.tx_renveilledocumentaire_pi2_wizicon.php';
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi3']='pi_flexform';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tt_content.list_type_pi3',
	$_EXTKEY . '_pi3',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi3', 'FILE:EXT:ren_veille_documentaire/flexform_ds_pi3.xml');


if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_renveilledocumentaire_pi3_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi3/class.tx_renveilledocumentaire_pi3_wizicon.php';
}

t3lib_extMgm::addStaticFile($_EXTKEY,'static/veille_documentaire/', 'Veille documentaire');

if (TYPO3_MODE == 'BE') {
    t3lib_extMgm::addModulePath('web_txrenveilledocumentaireM1', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');       
    t3lib_extMgm::addModule('web', 'txrenveilledocumentaireM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
}

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'tx_renveilledocumentaire_relations';

?>