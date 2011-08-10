<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 In Cité Solution <technique@in-cite.net>
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
 * Plugin 'Documents Watching display' for the 'ren_veille_documentaire' extension.
 *
 * @author Mickael PAILLARD <mickael@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_renveilledocumentaire
 */
class tx_renveilledocumentaire_pi2 extends tx_renveilledocumentaire_common {
	var $prefixId      = 'tx_renveilledocumentaire_pi2';
	var $prefixId1      = 'tx_renveilledocumentaire_pi1';	// Same as class name
	var $scriptRelPath = 'pi2/class.tx_renveilledocumentaire_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ren_veille_documentaire';	// The extension key.
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$sContent: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($sContent, $conf) {
		$this->conf = $conf;
		$this->pi_loadLL();
		$this->init();
		$this->pi_setPiVarDefaults();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
				
		if ((isset($_GET['tx_ttnews']['tt_news']))&&($_GET['tx_ttnews']['tt_news']>0)){
			$aNews=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,ren_veilledocumentaire_notices','tt_news', 'uid='.$_GET['tx_ttnews']['tt_news'].$this->cObj->enableFields('tt_news'));
			foreach($aNews as $iKey=>$aNew){
				$where='(tx_renveilledocumentaire_notices.actus='.$aNew['uid'].' OR tx_renveilledocumentaire_notices.actus LIKE \''.$aNew['uid'].',%\' OR tx_renveilledocumentaire_notices.actus LIKE \'%,'.$aNew['uid'].',%\' OR tx_renveilledocumentaire_notices.actus LIKE \'%,'.$aNew['uid'].'\')';
				$where2='tx_renveilledocumentaire_notices.uid IN('.$aNew['ren_veilledocumentaire_notices'].')';
			}
			$sListe='';
			//$aNotices2=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('tx_renveilledocumentaire_notices.*, tx_renveilledocumentaire_sources.icon', $this->aTables['notices'].' INNER JOIN tx_renveilledocumentaire_sources ON tx_renveilledocumentaire_notices.source = tx_renveilledocumentaire_sources.uid', $where.$this->cObj->enableFields($this->aTables['notices']), '', 'date DESC');
			$aNotices=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('tx_renveilledocumentaire_notices.*, tx_renveilledocumentaire_sources.icon', $this->aTables['notices'].' INNER JOIN tx_renveilledocumentaire_sources ON tx_renveilledocumentaire_notices.source = tx_renveilledocumentaire_sources.uid', $where.' OR '.$where2.$this->cObj->enableFields($this->aTables['notices']), '', 'date DESC');
			if(is_array($aNotices)){
				foreach($aNotices as $iKey=>$aNotice){
					$sLesVeilles='';
					$aSesVeilles=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('titre', $this->aTables['veilles'], 'uid IN('.$aNotice['veille'].')'.$this->cObj->enableFields($this->aTables['veilles']));
					foreach($aSesVeilles as $iKey=>$aSaVeille){
						$sLesVeilles.=($sLesVeilles!='')?', '.$aSaVeille['titre']:$aSaVeille['titre'];
					}
					$aLinkConf = array();
					$aLinkConf = array(
						'parameter' => ($this->conf['pagedetail']!='')?$this->conf['pagedetail']:$GLOBALS['TSFE']->id,
						'additionalParams' => '&'.$this->prefixId1.'[notice]=' . $aNotice['uid'],
						//'useCashHash' => true,
					);
					
					$aImgTSConfig = $this->conf['icon.'];
					$aImgTSConfig['file'] = "uploads/tx_renveilledocumentaire/".$aNotice['icon'];
					$sIcon = $this->cObj->IMG_RESOURCE( $aImgTSConfig );
					$sLogo = '';
					if(!$sIcon || $sIcon==""){
						$sLogo = "<img src='uploads/tx_renveilledocumentaire/".$aNotice['icon']."' />";
					}else{
						$sLogo = '<img src="' . $sIcon . '" />';
					}
					
					$aMarkerArray=array();
					$aMarkerArray['###TITRE###']=$this->cObj->typoLink($aNotice['titre'], $aLinkConf);
					$aMarkerArray['###ICON_SOURCE###']=$sLogo;
					$aMarkerArray['###DATE###']=strftime($this->conf['dateFormat'],$aNotice['date']);
					$aMarkerArray['###VEILLE_LABEL###']=$this->pi_getLL('veille_label');
					$aMarkerArray['###DATE_LABEL###']=$this->pi_getLL('date_label');
					$aMarkerArray['###VEILLE###']=$sLesVeilles;
					$sListe.= $this->viewTemplate('###TEMPLATE_TTNEWS_ITEM###',$aMarkerArray);
				}
			}
			if(is_array($aNotices2)){
				foreach($aNotices2 as $iKey=>$aNotice){
					$sLesVeilles='';
					$aSesVeilles=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('titre', $this->aTables['veilles'], 'uid IN('.$aNotice['veille'].')'.$this->cObj->enableFields($this->aTables['veilles']));
					foreach($aSesVeilles as $iKey=>$aSaVeille){
						$sLesVeilles.=($sLesVeilles!='')?', '.$aSaVeille['titre']:$aSaVeille['titre'];
					}
					$aLinkConf = array();
					$aLinkConf = array(
						'parameter' => ($this->conf['pagedetail']!='')?$this->conf['pagedetail']:$GLOBALS['TSFE']->id,
						'additionalParams' => '&'.$this->prefixId1.'[notice]=' . $aNotice['uid'],
						//'useCashHash' => true,
					);
					$aImgTSConfig = $this->conf['icon.'];
					$aImgTSConfig['file'] = "uploads/tx_renveilledocumentaire/".$aNotice['icon'];
					$sIcon = $this->cObj->IMG_RESOURCE( $aImgTSConfig );
					$sLogo = '';
					if(!$sIcon || $sIcon==""){
						$sLogo = "<img src='uploads/tx_renveilledocumentaire/".$aNotice['icon']."' />";
					}else{
						$sLogo = '<img src="' . $sIcon . '" />';
					}
					$aMarkerArray=array();
					$aMarkerArray['###TITRE###']=$this->cObj->typoLink($aNotice['titre'], $aLinkConf);
					$aMarkerArray['###ICON_SOURCE###']=$sLogo;
					$aMarkerArray['###DATE###']=strftime($this->conf['dateFormat'],$aNotice['date']);
					$aMarkerArray['###VEILLE_LABEL###']=$this->pi_getLL('veille_label');
					$aMarkerArray['###DATE_LABEL###']=$this->pi_getLL('date_label');
					$aMarkerArray['###VEILLE###']=$sLesVeilles;
					$sListe.= $this->viewTemplate('###TEMPLATE_TTNEWS_ITEM###',$aMarkerArray);
				}
			}
			$sListe=($sListe!='')?(($this->pi_getLL('titre'))?'<h2><span>'.$this->pi_getLL('titre').'</span></h2>':'').'<ul class="'.$this->prefixId.'_liste">'.$sListe.'</ul>':'';
			$aMarkerArray=array();
				$aMarkerArray['###LISTE###']=$sListe;
				$aMarkerArray['###CONF###']=t3lib_div::view_array($this->conf).count($veilles).'<br/><textarea cols="50" rows="8">'.$veille_select.'</textarea>'.t3lib_div::view_array($this->piVars);
			$sContent.= $this->viewTemplate('###TEMPLATE_TTNEWS_LIST###',$aMarkerArray);
		}
		
		
		return $this->pi_wrapInBaseClass($sContent);
	}

	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_veille_documentaire/pi2/class.tx_renveilledocumentaire_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_veille_documentaire/pi2/class.tx_renveilledocumentaire_pi2.php']);
}

?>