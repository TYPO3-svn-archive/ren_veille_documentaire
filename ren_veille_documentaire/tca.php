<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_renveilledocumentaire_sources'] = array (
	'ctrl' => $TCA['tx_renveilledocumentaire_sources']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,nom,icon'
	),
	'feInterface' => $TCA['tx_renveilledocumentaire_sources']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_renveilledocumentaire_sources',
				'foreign_table_where' => 'AND tx_renveilledocumentaire_sources.pid=###CURRENT_PID### AND tx_renveilledocumentaire_sources.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'nom' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_sources.nom',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			)
		),
		'icon' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_sources.icon',        
            'config' => array (
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'gif,png,jpeg,jpg',    
                'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],    
                'uploadfolder' => 'uploads/tx_renveilledocumentaire',
                'show_thumbs' => 1,    
                'size' => 1,    
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, nom, icon')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_renveilledocumentaire_keywords'] = array (
	'ctrl' => $TCA['tx_renveilledocumentaire_keywords']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'word,generic,specific_,associated,synonym'
	),
	'feInterface' => $TCA['tx_renveilledocumentaire_keywords']['feInterface'],
	'columns' => array (
		'word' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_keywords.word',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			)
		),
		'generic' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_keywords.generic',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_renveilledocumentaire_keywords',	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 5,	
				"MM" => "tx_renveilledocumentaire_keywords_generic_mm",
				'wizards' => array(
					'suggest' => array(
					'pidList' => '###CURRENT_PID###',
					'type' => 'suggest',
					),
				),
				'autoSizeMax' => 5,
			)
		),
		'specific_' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_keywords.specific',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_renveilledocumentaire_keywords',	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 50,	
				"MM" => "tx_renveilledocumentaire_keywords_generic_mm",
				'MM_opposite_field' => 'generic',
				'readOnly' => 1,
				'autoSizeMax' => 50,
			)
		),
		'associated' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_keywords.associated',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_renveilledocumentaire_keywords',	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 10,	
				"MM" => "tx_renveilledocumentaire_keywords_associated_mm",
				'wizards' => array(
					'suggest' => array(
					'pidList' => '###CURRENT_PID###',
					'type' => 'suggest',
					),
				),
				'autoSizeMax' => 10,
			)
		),
		'synonym' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_keywords.synonym',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'word;;;;1-1-1, generic, specific_, associated, synonym')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_renveilledocumentaire_auteurs'] = array (
	'ctrl' => $TCA['tx_renveilledocumentaire_auteurs']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'nom'
	),
	'feInterface' => $TCA['tx_renveilledocumentaire_auteurs']['feInterface'],
	'columns' => array (
		'nom' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_auteurs.nom',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'nom;;;;1-1-1')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_renveilledocumentaire_veilles'] = array (
	'ctrl' => $TCA['tx_renveilledocumentaire_veilles']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,starttime,endtime,titre,descriptif'
	),
	'feInterface' => $TCA['tx_renveilledocumentaire_veilles']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
			)
		),
		'titre' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_veilles.titre',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			)
		),
		'descriptif' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_veilles.descriptif',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, titre, descriptif')
	),
	'palettes' => array (
		'1' => array('showitem' => 'starttime, endtime')
	)
);



$TCA['tx_renveilledocumentaire_notices'] = array (
	'ctrl' => $TCA['tx_renveilledocumentaire_notices']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,starttime,endtime,titre,source,date,resume,mots_cles,auteurs,fichiers,url,voir_aussi,actus,veille'
	),
	'feInterface' => $TCA['tx_renveilledocumentaire_notices']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
			)
		),
		'titre' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_notices.titre',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			)
		),
		'source' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_notices.source',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_renveilledocumentaire_sources',	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 50,
				'wizards' => array(
					'suggest' => array(
					'type' => 'suggest',
					'pidList' => '###CURRENT_PID###',
					),
					"add" => Array(
                        "type" => "script",
                        "title" => "Create new record",
                        "icon" => "add.gif",
                        "params" => Array(
                            "table"=>"tx_renveilledocumentaire_sources",
                            "pid" => "###CURRENT_PID###",
                            "setValue" => "prepend"
                        ),
                        "script" => "wizard_add.php",
                    ),
				),
			)
		),
		'date' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_notices.date',		
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval' => 'date',
				'default' => time(),
				'checkbox' => '1',
			)
		),
		'resume' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_notices.resume',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
			)
		),
		'mots_cles' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_notices.mots_cles',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_renveilledocumentaire_keywords',	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 50,
				'wizards' => array(
					'suggest' => array(
					'type' => 'suggest',
					'pidList' => '###CURRENT_PID###',
					),
					"add" => Array(
                        "type" => "script",
                        "title" => "Create new record",
                        "icon" => "add.gif",
                        "params" => Array(
                            "table"=>"tx_renveilledocumentaire_keywords",
                            "pid" => "###CURRENT_PID###",
                            "setValue" => "prepend"
                        ),
                        "script" => "wizard_add.php",
                    ),
				),
			)
		),
		'auteurs' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_notices.auteurs',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_renveilledocumentaire_auteurs',	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 50,
				'wizards' => array(
					'suggest' => array(
					'type' => 'suggest',
					'pidList' => '###CURRENT_PID###',
					),
					"add" => Array(
                        "type" => "script",
                        "title" => "Create new record",
                        "icon" => "add.gif",
                        "params" => Array(
                            "table"=>"tx_renveilledocumentaire_auteurs",
                            "pid" => "###CURRENT_PID###",
                            "setValue" => "prepend"
                        ),
                        "script" => "wizard_add.php",
                    ),
				),
			)
		),
		'fichiers' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_notices.fichiers',		
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => '',	
				'disallowed' => 'php,php3',	
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],	
				'uploadfolder' => 'uploads/tx_renveilledocumentaire/',
				'show_thumbs' => 1,	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 50,
			)
		),
		'url' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_notices.url',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'wizards' => array(
					'_PADDING' => 2,
					'link' => array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					),
				),
				'eval' => 'trim',
			)
		),
		'voir_aussi' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_notices.voir_aussi',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_renveilledocumentaire_notices',	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 50,
				'wizards' => array(
					'suggest' => array(
					'pidList' => '###CURRENT_PID###',
					'type' => 'suggest',
					),
				),
			)
		),
		'actus' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_notices.actus',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tt_news',	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 50,
				'wizards' => array(
					'suggest' => array(
					'pidList' => '###CURRENT_PID###',
					'type' => 'suggest',
					),
				),
			)
		),
		'veille' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_notices.veille',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_renveilledocumentaire_veilles',	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 50,
				'wizards' => array(
					'suggest' => array(
					'type' => 'suggest',
					'pidList' => '###CURRENT_PID###',
					),
					"add" => Array(
                        "type" => "script",
                        "title" => "Create new record",
                        "icon" => "add.gif",
                        "params" => Array(
                            "table"=>"tx_renveilledocumentaire_veilles",
                            "pid" => "###CURRENT_PID###",
                            "setValue" => "prepend"
                        ),
                        "script" => "wizard_add.php",
                    ),
				),
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, titre, source, date, resume, mots_cles, auteurs, fichiers, url, voir_aussi, actus, veille')
	),
	'palettes' => array (
		'1' => array('showitem' => 'starttime, endtime')
	)
);



$TCA['tx_renveilledocumentaire_fichiers_stats'] = array (
	'ctrl' => $TCA['tx_renveilledocumentaire_fichiers_stats']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'fichier'
	),
	'feInterface' => $TCA['tx_renveilledocumentaire_fichiers_stats']['feInterface'],
	'columns' => array (
		'fichier' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_fichiers_stats.fichier',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'fichier;;;;1-1-1')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>