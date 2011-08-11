<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 In Cité Solution <technique@in-cite.net>
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

require_once(t3lib_extMgm::extPath('ren_veille_documentaire') . 'class.tx_renveilledocumentaire_common.php');


/**
 * Plugin 'Menu' for the 'ren_veille_documentaire' extension.
 *
 * @author Emilie PRUD'HOMME <emilie@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_renveilledocumentaire
 */
class tx_renveilledocumentaire_pi3 extends tx_renveilledocumentaire_common {
	var $prefixId      = 'tx_renveilledocumentaire_pi3';
	var $prefixId1      = 'tx_renveilledocumentaire_pi1';	// Same as class name
	var $scriptRelPath = 'pi3/class.tx_renveilledocumentaire_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ren_veille_documentaire';	// The extension key.
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_loadLL();
		$this->init();
		$this->pi_setPiVarDefaults();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		
		$content = $this->renderMenu();
		return $this->pi_wrapInBaseClass($content);
	}
	
	function init() {
		parent::init();
		
		if (!$this->conf['detailsveille'])
			$this->conf['detailsveille'] = $GLOBALS['TSFE']->id;
	}
	
	/**
	 * View menu
	 *
	 * @return html menu
	 */
	function renderMenu() {
		$template = $this->viewTemplate('###TEMPLATE_MENU###');
		
		$subparts = array();
		$markers = array(
			'###TITLE###' => $this->pi_getLL('menu_title'),
		);
		
		$veilles = $this->getLastVeilles($this->conf['limit']);
		
		if (!is_array($veilles) || empty($veilles)) {
			$subparts['###TEMPLATE_MENU_NOTEMPTY###'] = '';
		} else {
			$subpart_item = $this->cObj->getSubpart($template, '###TEMPLATE_MENU_ITEM###');
			$output_item = '';
			$prefixId = $this->prefixId;
			$this->prefixId = 'tx_renveilledocumentaire_pi1';
			foreach ($veilles as $veille) {
				$markers_veille = array(
					'###URL###' => $this->pi_linkTP_keepPIvars_url(array('veille' => $veille['uid']), 0, 1, $this->conf['detailsveille']),
					'###NAME###' => $veille['titre'],
					'###DESCRIPTION###' => $veille['descriptif'],
				);
				$output_item .= $this->cObj->substituteMarkerArray($subpart_item, $markers_veille);	
			}
			$this->prefixId = $prefixId;
			$subparts['###TEMPLATE_MENU_ITEM###'] = $output_item;
		}
		return $this->cObj->substituteMarkerArrayCached($template, $markers, $subparts);
	}
	
	/**
	 * Get Last Veilles
	 *
	 * @param string/int $limit	limit results
	 * @return array database result
	 */
	function getLastVeilles($limit = '') {
		$addWhere = '';
		
		$data = t3lib_div::_GP('tx_renveilledocumentaire_pi1');
		if ($data['veille']) 
			$addWhere .= ' AND `' . $this->aTables['veilles'] . '`.`uid` != ' . $data['veille'];
		
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'`' . $this->aTables['veilles'] . '`.`uid`,
				`' . $this->aTables['veilles'] . '`.`titre` ,
				`' . $this->aTables['veilles'] . '`.`descriptif`', 
			'`' . $this->aTables['veilles'] . '`', 
			'1 ' . $this->cObj->enableFields($this->aTables['veilles']) . $addWhere, 
			'', 
			'`' . $this->aTables['veilles'] . '`.`crdate` DESC',
			$limit
		);
	}
			
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_veille_documentaire/pi3/class.tx_renveilledocumentaire_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_veille_documentaire/pi3/class.tx_renveilledocumentaire_pi3.php']);
}

?>