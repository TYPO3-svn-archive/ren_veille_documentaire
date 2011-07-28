<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 In Cite <contact@in-cite.net>
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */


// DEFAULT initialization of a module [BEGIN]
/*unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');*/


$LANG->includeLLFile('EXT:ren_veille_documentaire/mod1/locallang.xml');
$LANG->includeLLFile('EXT:lang/locallang_mod_web_list.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]
require_once(PATH_t3lib . 'class.t3lib_clipboard.php');
require_once(t3lib_extMgm::extPath('ren_veille_documentaire', 'mod1/class.documentRecordList.php'));
require_once(t3lib_extMgm::extPath('ren_veille_documentaire', 'mod1/class.tx_renveilledocumentaire_renderer.php'));

/**
 * Module 'Veille documentaire' for the 'ren_veille_documentaire' extension.
 *
 * @author	Mickael PAILLARD <mickael@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_renveilledocumentaire
 */
class  tx_renveilledocumentaire_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();
		// Record list delete command support.
		$this->pointer = t3lib_div::_GP('pointer');
		$this->cmd = t3lib_div::_GP('cmd');
		$this->cmd_table = t3lib_div::_GP('cmd_table');
		if ($this->cmd=='delete')	{
			$this->include_once[]=PATH_t3lib.'class.t3lib_tcemain.php';
		}
		/*
		if (t3lib_div::_GP('clear_all_cache'))	{
			$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('function1'),
				'2' => $LANG->getLL('function2'),
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
	
		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="post" enctype="multipart/form-data" name="notice_search">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'],-50);

			// Render content:
			$this->content = '';
			$this->moduleContent();
			$content = $this->content;
			$this->content = '';
			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);
			$this->content .= $content;



			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{
		global $LANG;
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$content = $this->manageNotices();
				/*$content.='
					<hr />
					<br />This is the GET/POST vars sent to the script:<br />'.
					'GET:'.t3lib_div::view_array($_GET).'<br />'.
					'POST:'.t3lib_div::view_array($_POST).'<br />'.
					'';*/
				$this->content.=$this->doc->section($LANG->getLL('function1').' :',$content,0,1);
			break;
			case 2:
				$filterstats = t3lib_div::_GP('filterstats');
				$content='<label for="filterstatssource">' . htmlspecialchars($GLOBALS['LANG']->sL('source')) . '</label><select size="1" id="filterstatssource" title="' . htmlspecialchars($GLOBALS['LANG']->sL('source')) . '" name="filterstats[source]">
		<option value=""></option>' .
	implode(
		'',
		array_map(
			create_function(
				'$val',
				'return \'
		<option\' . (($val[\'uid\'] == ' . intval($filterstats['source']) . ') ? (\' selected="selected"\') : (\'\')) . \' value="\' . $val[\'uid\'] . \'">\' . htmlspecialchars($val[\'nom\']) . \'</option>\';'
			),
			$GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'uid, nom',
				'tx_renveilledocumentaire_sources',
				'1 ' . t3lib_BEfunc::BEenableFields('tx_renveilledocumentaire_sources') . t3lib_BEfunc::deleteClause('tx_renveilledocumentaire_sources')
			)
		)
	) . '
	</select>
			<label for="filterstatsdebut">'. htmlspecialchars($GLOBALS['LANG']->sL('debut')) .' </label><input type="text" size="10" id="filterstatsdebut" title="' . htmlspecialchars($GLOBALS['LANG']->sL('debut')) . '" name="filterstats[debut]" value="' . htmlspecialchars($filterstats['debut']) . '" />
	<label for="filterstatsfin">'. htmlspecialchars($GLOBALS['LANG']->sL('fin')) .' </label><input type="text" size="10" id="filterstatsfin" title="' . htmlspecialchars($GLOBALS['LANG']->sL('fin')) . '" name="filterstats[fin]" value="' . htmlspecialchars($filterstats['fin']) . '" />(JJ/MM/AAAA)
	<br/><input type="submit" value="' . htmlspecialchars($LANG->getLL('applyfilter')) . '" /><br/><br/>';
				$content.=$this->manageStats();
				/*$content.='
					<hr />
					<br />This is the GET/POST vars sent to the script:<br />'.
					'GET:'.t3lib_div::view_array($_GET).'<br />'.
					'POST:'.t3lib_div::view_array($_POST).'<br />'.
					'';*/
				$this->content.=$this->doc->section($LANG->getLL('function2').':',$content,0,1);
			break;
		}
	}
	
	/**
	* Displays the notices management interface.
	* 
	* @return string The HTML output.
	*/
	function manageNotices() {
		global $LANG;
		$null = null;
		$this->renderer = t3lib_div::makeInstance('tx_renveilledocumentaire_renderer', $this, $null);
		$this->renderer->returnUrl = t3lib_div::linkThisUrl(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'),$_POST);
		$this->renderer->filter = true;
		$this->renderer->pageinfo = $this->pageinfo;
		$params = '&edit[tx_renveilledocumentaire_notices][' . $this->id . ']=new';
		return $this->renderer->showList('tx_renveilledocumentaire_notices', '
			<strong>
				<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick($params, $this->doc->backPath)) . '" title="' . $LANG->getLL('create_new_record') . '">
					<img ' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/new_el.gif', 'width="11" height="12"') . ' alt="' . $LANG->getLL('create_new_record') . '" align="middle" />
				</a>
				' . $LANG->getLL('no_document') . '
			</strong>'
		) . $this->doc->sectionEnd();
	}
	
	/**
	* Displays the notices' stats management interface.
	* 
	* @return string The HTML output.
	*/
	function manageStats() {
		$filter = t3lib_div::_GP('filterstats');
		$addWhere='1';
		if (isset($filter['source']) && is_numeric($filter['source']))
			$addWhere .= ' AND (`' . $table . '`.`source` = ' . $filter['source'].' OR `' . $table . '`.`source` LIKE \'' . $filter['source'].',%\'  OR `' . $table . '`.`source` LIKE \'%,' . $filter['source'].',%\' OR `' . $table . '`.`source` LIKE \'%,' . $filter['source'].'\')';
		if (isset($filter['debut']) && !empty($filter['debut'])){
			$date=explode('/',$filter['debut']);
			$debut=mktime(0,0,0,$date[1],$date[0],$date[2]);
			$addWhere .= ' AND `' . $table . '`.`date` > ' . $debut;
		}
		if (isset($filter['fin']) && !empty($filter['fin'])){
			$date=explode('/',$filter['fin']);
			$fin=mktime(0,0,0,$date[1],$date[0],$date[2]);
			$addWhere .= ' AND `' . $table . '`.`date` < ' . $fin;
		}
		$notices=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_renveilledocumentaire_notices',
			$addWhere . t3lib_BEfunc::BEenableFields('tx_renveilledocumentaire_notices') . t3lib_BEfunc::deleteClause('tx_renveilledocumentaire_notices'),
			'',
			'date DESC'
		);
			$stats='';
		foreach ($notices as $key=>$notice){
			$stats.='<br/><strong>'.$notice['titre'].'</strong> '.strftime('%d/%m/%Y',$notice['date']).'<br/>';
			if ($notice['fichiers']!=''){
				$fichiers=explode(',',$notice['fichiers']);
				$statsTable='';
				foreach($fichiers as $key=>$fichier){
					$vues=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
						'*',
						'tx_renveilledocumentaire_fichiers_stats',
						'fichier=\'uploads/tx_renveilledocumentaire/'.$fichier.'\''. t3lib_BEfunc::BEenableFields('tx_renveilledocumentaire_fichiers_stats') . t3lib_BEfunc::deleteClause('tx_renveilledocumentaire_fichiers_stats')
					);
					$statsTable.='<tr><td>'.$fichier.'</td><td style="text-align: right;">'.((is_array($vues))?count($vues):'0').' vue(s)</td></tr>';
				}
				$stats.=($statsTable!='')?'<table width="100%">'.$statsTable.'</table>':'';
			}
			else{
				$stats.='<p>Pas de fichier</p>';
			}
								
		}
		return $stats;
		
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_veille_documentaire/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_veille_documentaire/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_renveilledocumentaire_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>