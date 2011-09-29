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
class tx_renveilledocumentaire_pi1 extends tx_renveilledocumentaire_common {
	var $prefixId      = 'tx_renveilledocumentaire_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_renveilledocumentaire_pi1.php';	// Path to this script relative to the extension dir.
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
				
		$sContent='';
			
		if((isset($this->piVars['notice']))&&(($this->piVars['notice'])>0)&&(($this->conf['detailsnotice']=='')||(!isset($this->conf['detailsnotice'])))){
			$sContent .= $this->renderNotice($this->piVars['notice']);
		}
		else{
			$sContent .= $this->renderListVeilles();
		}
		return $this->pi_wrapInBaseClass($sContent);
	}
	
	/**
	 * Render veilles list
	 *
	 * @return html
	 */
	function renderListVeilles() {
		$sContent = '';
		
		$sWhere='';
		$iPiVarsVeille=0;
		$veillesNames = '';
		if((isset($this->piVars['veille']))&&($this->piVars['veille']>0)) $iPiVarsVeille=$this->piVars['veille'];
		if($this->conf['veilles']!='' || $iPiVarsVeille){
			$aVeillesAffichees=array();
			$aVeilles=explode(',',$this->conf['veilles']);
			$aVeillesAffichees=($iPiVarsVeille>0) ? array(0=>$iPiVarsVeille):$aVeilles;
	
			if(is_array($aVeillesAffichees)){
				$outputVeillesNames = array();
				foreach ($aVeillesAffichees as $iKey=>$iVeilleUid){
					$sOp=((isset($this->conf['operateur']))&&($this->conf['operateur']>0))?' AND ':' OR ';
					$sWhere.=($sWhere=='')?'':$sOp;
					$sWhere.='(veille='.$iVeilleUid.' OR veille LIKE \''.$iVeilleUid.',%\' OR veille LIKE \'%,'.$iVeilleUid.',%\' OR veille LIKE \'%,'.$iVeilleUid.'\')';
					$outputVeillesNames[] = $iVeilleUid;
				}
				
				$veilles = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'`' . $this->aTables['veilles'] .'`.`titre`', 
					'`' . $this->aTables['veilles'] .'`', 
					'`' . $this->aTables['veilles'] .'`.`uid` IN (' . implode(',', $outputVeillesNames) .') ' . $this->cObj->enableFields($this->aTables['veilles']),
					'',
					'`' . $this->aTables['veilles'] .'`.`titre`'					
				);
				if (is_array($veilles) && !empty($veilles)) {
					$outputVeillesNames = array();
					foreach ($veilles as $veille) {
						$outputVeillesNames[] = $veille['titre'];
					}
					$veillesNames = implode(', ', $outputVeillesNames);
				}
			}
			
			if ((count($aVeilles))>1){
				$sVeilleSelect = $this->renderFormSelectVeille($aVeilles);
			}
			else $sVeilleSelect='';
		}
		else{
			$sVeilleSelect = $this->renderFormSelectVeille();
		}
		
		$sLimit='';
		$iNbPages=1;
		if($this->conf['maxnotice']>0){
			if((isset($this->piVars['page']))&&($this->piVars['page']>1)&&($this->conf['nbnoticepage']>0)){
				$sLimit=$this->conf['nbnoticepage']*($this->piVars['page']-1).','.min($this->conf['nbnoticepage'],($this->conf['maxnotice']-($this->conf['nbnoticepage']*($this->piVars['page']-1))));
			}
			else{
				if($this->conf['nbnoticepage']>0){
					$sLimit=min($this->conf['nbnoticepage'],$this->conf['maxnotice']);
				}
				else{
					$sLimit=$this->conf['maxnotice'];
				}
			}
			
			if($this->conf['nbnoticepage']>0){	
				$rCount = $GLOBALS['TYPO3_DB']->exec_SELECTquery('COUNT(uid)', $this->aTables['notices'], $sWhere.$this->cObj->enableFields($this->aTables['notices']));
				$iCount=0;
				if($aCount= $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rCount)){
					$iCount=$aCount['COUNT(uid)'];
				}

				$iNbPages  = ceil(min($iCount,$this->conf['maxnotice'])/$this->conf['nbnoticepage']);
			}
		}
		else{
			if((isset($this->piVars['page']))&&($this->piVars['page']>1)&&($this->conf['nbnoticepage']>0)){
				$sLimit=$this->conf['nbnoticepage']*($this->piVars['page']-1).','.$this->conf['nbnoticepage'];
			}
			else{
				if($this->conf['nbnoticepage']>0){
					$sLimit=$this->conf['nbnoticepage'];
				}
			}
			if($this->conf['nbnoticepage']>0){
				$rCount = $GLOBALS['TYPO3_DB']->exec_SELECTquery('COUNT(uid)', $this->aTables['notices'], $where.$this->cObj->enableFields($this->aTables['notices']));
				$iCount=0;
				if($aCount= $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rCount)){
					$iCount=$aCount['COUNT(uid)'];
				}
				$iNbPages  = ceil($iCount/$this->conf['nbnoticepage']);
			}
		}
		
		//$sContent.=t3lib_div::view_array($this->conf);
		$sPagesLinks='';
		if($iNbPages>1){
			for ($iPageNum = 1; $iPageNum <= $iNbPages; $iPageNum++) {
				$aLinkConf = array();
				$aLinkConf = array(
					'parameter' => $GLOBALS['TSFE']->id,
					'addQueryString' => 1,
					'additionalParams' => '&'.$this->prefixId.'[page]=' . $iPageNum.(($iPiVarsVeille>0)?'&'.$this->prefixId.'[veille]='.$iPiVarsVeille:''),
					//'useCashHash' => true,
				);
				$aLinkConf['addQueryString.']['exclude']=$this->prefixId.'[page],id';
				$sCourant=($iPageNum==$this->piVars['page'])?' class="courant"':'';
				$sCourant=(!(isset($this->piVars['page']))&& ($iPageNum==1))?' class="courant"':$sCourant;
				$sPagesLinks.='<li'.$sCourant.'>'.$this->cObj->typoLink($iPageNum, $aLinkConf).'</li>';
			}
			$sPagesLinks='<ul class="'.$this->prefixId.'_navpages">'.$sPagesLinks.'</ul>';
		}
		
		$sListe='';
		$aNotices = $this->getNotices(0, ' AND ' . $sWhere, '', $sLimit);
		if(is_array($aNotices) && !empty($aNotices)){
			$template = $this->viewTemplate('###TEMPLATE_LIST###');
			$subpart_item = $this->cObj->getSubpart($template, '###TEMPLATE_NOTICE###');
			
			$indice = 1;
			foreach($aNotices as $iKey=>$aNotice){
				$dataNotice = $this->getMarkersNotice($aNotice, false);
				$markers = $dataNotice[0];
				$indice ++;
				$markers['###ALT###'] = ($indice%2) ? 'alt' : '';
				$sListe .= $this->cObj->substituteMarkerArrayCached($subpart_item, $markers, $dataNotice[1]);
			}
		}
				
		$aMarkerArray=array();
		$aMarkerArray['###VEILLE_SELECT###']=((isset($this->conf['operateur']))&&($this->conf['operateur']>0))?'':$sVeilleSelect;
		$aMarkerArray['###NAV_PAGES###']=$sPagesLinks;
		$aMarkerArray['###VEILLES###']=$veillesNames;
		
		$subpartArray = array();
		$subpartArray['###TEMPLATE_NOTICE###'] = $sListe;
		if (!$sListe)
			$subpartArray['###TEMPLATE_NOTICES###'] = '';
			
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalListFields'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalListFields'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$_procObj->additionalListFields($aMarkerArray, $subpartArray, $this->conf, $this);
			}
		}
		
		$sContent.= $this->viewTemplate('###TEMPLATE_LIST###',$aMarkerArray, $subpartArray);
		
		return $sContent;
	}
	
	/**
	 * Get notices rows
	 *
	 * @param int notice uid
	 * @return array
	 */
	function getNotices($uid = 0, $addWhere = '', $order = '', $limit = 50) {
		if ($uid) 
			$addWhere .= ' AND `' . $this->aTables['notices'] . '`.`uid` = ' . $uid;
		
		if (!$order) {
			switch ($this->conf['sorting']) {
				case 'DATE':
					$order = '`' . $this->aTables['notices'] . '`.`crdate` DESC';
				break;
				case 'SORTING':
					$order = '`' . $this->aTables['notices'] . '`.`sorting`';
				break;
				case 'TITRE':
				default:
					$order = '`' . $this->aTables['notices'] . '`.`titre`';
			}
		}
		
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['selectConfNotices'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['selectConfNotices'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$_procObj->selectConfNotices($addWhere, $order, $limit, $this->conf, $this);
			}
		}
		
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'`' . $this->aTables['notices'] . '`.`uid`,
				`' . $this->aTables['notices'] . '`.`veille`,
				`' . $this->aTables['notices'] . '`.`source`,
				`' . $this->aTables['notices'] . '`.`resume`,
				`' . $this->aTables['notices'] . '`.`auteurs`,
				`' . $this->aTables['notices'] . '`.`mots_cles`,
				`' . $this->aTables['notices'] . '`.`fichiers`,
				`' . $this->aTables['notices'] . '`.`url`,
				`' . $this->aTables['notices'] . '`.`voir_aussi`,
				`' . $this->aTables['notices'] . '`.`actus`,
				`' . $this->aTables['notices'] . '`.`titre`,
				`' . $this->aTables['notices'] . '`.`date`,
				`' . $this->aTables['sources'] . '`.`icon`,
				`' . $this->aTables['sources'] . '`.`nom`', 
			'`' . $this->aTables['notices'] . '`
				LEFT OUTER JOIN `' . $this->aTables['sources'] . '`
					ON `' . $this->aTables['notices'] . '`.`source` = `' . $this->aTables['sources'] . '`.`uid`', 
			'1 ' . $this->cObj->enableFields($this->aTables['notices']) . $addWhere,
			'',
			$order,
			$limit
		);
	}
	
	/**
	 * Render details notice
	 *
	 * @param int notice uid
	 * @return html
	 */
	function renderNotice($uid) {
		$sContent = '';
		
		$aNotices = $this->getNotices($uid);
		if (is_array($aNotices)){
			foreach($aNotices as $iKey=>$aNotice){
				$dataNotice = $this->getMarkersNotice($aNotice);
				$sContent.= $this->viewTemplate('###TEMPLATE_DETAIL###', $dataNotice[0], $dataNotice[1]);
			}
		}
		return $sContent;
	}	
		
	/**
	 * Get Markers details notice
	 *
	 * @param array notice row
	 * @param boolean view details or not
	 * @return array
	 */
	function getMarkersNotice($aNotice, $details = true) {
		
		// details link
		if (!$details) {
			$aLinkConf = array();
			$aLinkConf = array(
				'parameter' => ($this->conf['detailsnotice']!='')?$this->conf['detailsnotice']:$GLOBALS['TSFE']->id,
				'additionalParams' => '&'.$this->prefixId.'[notice]=' . $aNotice['uid'],
				//'useCashHash' => true,
			);
		}
		
		// veille
		$sLesVeilles='';
		$aSesVeilles=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('titre', $this->aTables['veilles'], 'uid IN('.$aNotice['veille'].')'.$this->cObj->enableFields($this->aTables['veilles']));
		if(is_array($aSesVeilles)){
			foreach($aSesVeilles as $iKey=>$aSaVeille){
				$sLesVeilles.=($sLesVeilles!='')?', '.$aSaVeille['titre']:$aSaVeille['titre'];
			}
		}	

		// sources
		$sLesSources='';
		$sLesSourcesIcon = '';
		$aImgTSConfig = $this->conf['icon.'];
		$aSesSources=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('nom, icon', $this->aTables['sources'], 'uid IN('.$aNotice['source'].')'.$this->cObj->enableFields($this->aTables['sources']));
		if(is_array($aSesSources)){
			foreach($aSesSources as $iKey=>$aSaSource){
				if (!$aSaSource['icon'])
					continue;
					
				$sLesSources.=($sLesSources!='')?', '.$aSaSource['nom']:$aSaSource['nom'];
				$aImgTSConfig['file'] = "uploads/tx_renveilledocumentaire/".$aSaSource['icon'];
				$sIcon = $this->cObj->IMG_RESOURCE( $aImgTSConfig );
				if(!$sIcon || $sIcon==""){
					$sLesSourcesIcon .= "<img src='uploads/tx_renveilledocumentaire/".$aSaSource['icon']."' />";
				}else{
					$sLesSourcesIcon .= '<img src="' . $sIcon . '" />';
				}
			}
		}
		
		// mots clés 
		$sLesMotsCles='';
		$aSesMotsCles=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('word AS mot', $this->aTables['mots_cles'], 'uid IN('.$aNotice['mots_cles'].')'.$this->cObj->enableFields($this->aTables['mots_cles']));
		if(is_array($aSesMotsCles)){
		foreach($aSesMotsCles as $iKey=>$aSonMotCle){
			$sLesMotsCles.=($sLesMotsCles!='')?', '.$aSonMotCle['mot']:$aSonMotCle['mot'];
		}
		}
		
		// auteurs
		$sLesAuteurs='';
		$aSesAuteurs=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('nom', $this->aTables['auteurs'], 'uid IN('.$aNotice['auteurs'].')'.$this->cObj->enableFields($this->aTables['auteurs']));
		if(is_array($aSesAuteurs)){
			foreach($aSesAuteurs as $iKey=>$aSonAuteur){
				$sLesAuteurs.=($sLesAuteurs!='')?', '.$aSonAuteur['nom']:$aSonAuteur['nom'];
			}
		}
		
		// fichiers
		$aFileArray=explode(',',$aNotice['fichiers']);
		$aFilesData = array();
		$aLienConf=array();
		$aLienConf['ATagParams']='class="'.$this->extKey.'_lien"';
		$aLienConf['target']='_blank';
		$aLienConf['fileTarget']='_blank';
					
		$sLesFichiers='';
		if(is_array($aFileArray)){
		foreach($aFileArray as $iKey => $sFileName)	{
			$sAbsPath = t3lib_div::getFileAbsFileName('uploads/tx_renveilledocumentaire/'.$sFileName);
			if (@is_file($sAbsPath))	{
				$aLienConf['parameter']='uploads/tx_renveilledocumentaire/'.$sFileName;
				$sLesFichiers.='<li>'.$this->cObj->typoLink($sFileName,$aLienConf).'</li>';
			}
		}
		}
		$sLesFichiers=($sLesFichiers!='')?'<ul class="'.$this->prefixId.'_fichiers">'.$sLesFichiers.'</ul>':'';
		
		$aLienConfUrl=array();
		$aLienConfUrl['parameter']=$aNotice['url'];
		$aLienConfUrl['target']='_blank';
		$aLienConfUrl['fileTarget']='_blank';
		
		// voir aussi
		$sVoirAussi='';
		$aSesVoirAussi=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('titre,uid', $this->aTables['notices'], 'uid IN('.$aNotice['voir_aussi'].')'.$this->cObj->enableFields($this->aTables['notices']));
		$aLinkConfAussi=array();
		$aLinkConfAussi['parameter']=($this->conf['detailsnotice']!='')?$this->conf['detailsnotice']:$GLOBALS['TSFE']->id;
		if(is_array($aSesVoirAussi)){
			foreach($aSesVoirAussi as $iKey=>$aSonVoirAussi){
				$aLinkConfAussi['additionalParams']='&'.$this->prefixId.'[notice]=' . $aSonVoirAussi['uid'];
				$sVoirAussi.='<li>'.$this->cObj->typoLink($aSonVoirAussi['titre'],$aLinkConfAussi).'</li>';
			}
		}
		$sVoirAussi=($sVoirAussi!='')?'<ul class="'.$this->prefixId.'_voir_aussi">'.$sVoirAussi.'</ul>':'';
		
		// actus
		$sActus='';
		$aSesActus=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('title,uid', 'tt_news', 'uid IN('.$aNotice['actus'].') '.$this->cObj->enableFields('tt_news'));
		$aLinkConfActus=array();
		$aLinkConfActus['parameter']=($this->conf['pagenews']!='')?$this->conf['pagenews']:$GLOBALS['TSFE']->id;
		if (is_array($aSesActus)){
			foreach($aSesActus as $iKey=>$sonactu){
				$aLinkConfActus['additionalParams']='&tx_ttnews[tt_news]=' . $sonactu['uid'];
				$sActus.='<li>'.$this->cObj->typoLink($sonactu['title'],$aLinkConfActus).'</li>';
			}
		}
		$sActus=($sActus!='')?'<ul class="'.$this->prefixId.'_actus">'.$sActus.'</ul>':'';
			
		$resume = $this->cObj->parseFunc($aNotice['resume'], $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.']);
		if ($this->conf['resumecrop'])
			$resume = $this->cObj->stdWrap($resume, array('cropHTML' => $this->conf['resumecrop']));
			
		$aMarkerArray = array(
			'###TITRE###' => $this->cObj->typoLink($aNotice['titre'], $aLinkConf),
			'###MORE###' => $this->cObj->typoLink($this->pi_getLL('more'), $aLinkConf),
			'###VEILLE_LABEL###' => $this->pi_getLL('veille_label'),
			'###SOURCE###' => $sLesSources,
			'###ICON_SOURCE###' => $sLesSourcesIcon,
			'###SOURCE_LABEL###' => $this->pi_getLL('source_label'),
			'###MOTS_CLES###' => $sLesMotsCles,
			'###MOTS_CLES_LABEL###' => $this->pi_getLL('mots_cles_label'),
			'###DATE###' => strftime($this->conf['dateFormat'],$aNotice['date']),
			'###DATE_LABEL###' => $this->pi_getLL('date_label'),
			'###AUTEURS###' => $sLesAuteurs,
			'###AUTEURS_LABEL###' => $this->pi_getLL('auteurs_label'),
			'###RESUME###' => $resume,
			'###RESUME_LABEL###' => $this->pi_getLL('resume_label'),
			'###FICHIERS###' => $sLesFichiers,
			'###FICHIERS_LABEL###' => $this->pi_getLL('fichiers_label'),
			'###URL###' => $this->cObj->typoLink($aNotice['url'],$aLienConfUrl),
			'###URL_LABEL###' => $this->pi_getLL('url_label'),
			'###VOIR_AUSSI###' => $sVoirAussi,
			'###VOIR_AUSSI_LABEL###' => ($sVoirAussi!='')? $this->pi_getLL('voir_aussi_label') : '',
			'###ACTUS###' => $sActus,
			'###ACTUS_LABEL###' => ($sActus!='')? $this->pi_getLL('actus_label') : '',
		);
		
		if (!$details) {
			$aMarkerArray['###VEILLE###']=((isset($this->conf['operateur']))&&($this->conf['operateur']>0))?'':$sLesVeilles;
		} else {
			$aMarkerArray['###VEILLE###']=$sLesVeilles;
		}
		
		$subpartArray = array();
		if (!$aNotice['titre']) 
			$subpartArray['###SUBPART_TITRE###'] = '';
		if (!$sLesSources) 
			$subpartArray['###SUBPART_SOURCE###'] = '';
		if (!$sLesMotsCles) 
			$subpartArray['###SUBPART_MOTS_CLES###'] = '';
		if (!$sLesAuteurs) 
			$subpartArray['###SUBPART_AUTEURS###'] = '';
		if (!$aNotice['resume']) 
			$subpartArray['###SUBPART_RESUME###'] = '';
		if (!$sLesFichiers) 
			$subpartArray['###SUBPART_FICHIERS###'] = '';
		if (!$aNotice['url']) 
			$subpartArray['###SUBPART_URL###'] = '';
		if (!$sVoirAussi) 
			$subpartArray['###SUBPART_VOIR_AUSSI###'] = '';
		if (!$sActus) 
			$subpartArray['###SUBPART_ACTUS###'] = '';
		
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalNoticeSingleFields'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalNoticeSingleFields'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$_procObj->additionalNoticeSingleFields($aMarkerArray, $subpartArray, $aNotice, $this->conf, $this);
			}
		}
		
		return array($aMarkerArray, $subpartArray);
	}
	
	/**
	 * Render form to select veilles
	 *
	 * @param array/null array of veilles 
	 * @return html
	 */
	function renderFormSelectVeille($veilles = null) {
		$sVeilleSelect ='
		<form action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).'" method="post">
			<fieldset>
				<select name="'.$this->prefixId.'[veille]">
					<option></option>';
		
		if ($veilles && is_array($veilles) && !empty($veilles)) {
			foreach ($aVeilles as $iKey=>$iVeilleUid){
				$aRVeilles=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'uid,titre', 
					$this->aTables['veilles'], 
					'uid=' . $iVeilleUid . $this->cObj->enableFields($this->aTables['veilles']), 
					'', 
					'titre'
				);
				foreach($aRVeilles as $iKey=>$aOptVeille){
					$sSelected=($aOptveille['uid']==$iPiVarsVeille)?' selected="selected"':'';
					$sVeilleSelect.='
					<option value="'.$aOptVeille['uid'].'"'.$sSelected.'>'.$aOptVeille['titre'].'</option>';
				}
			}
		} else {
			$aRVeilles=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'uid,titre', 
				$this->aTables['veilles'], 
				'1 ' . $this->cObj->enableFields($this->aTables['veilles']), 
				'', 
				'titre'
			);
			if(is_array($aRVeilles)){
				foreach($aRVeilles as $iKey=>$aOptVeille){
					$sSelected=($aOptVeille['uid']==$iPiVarsVeille)?' selected="selected"':'';
					$sVeilleSelect.='
					<option value="'.$aOptVeille['uid'].'"'.$sSelected.'>'.$aOptVeille['titre'].'</option>';
				}
			}
		}
			
		$sVeilleSelect.='
				</select>
				<input type="submit" value="OK" class="submit"/>
			</fieldset>
		</form>
		';
			
		return $sVeilleSelect;
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_veille_documentaire/pi1/class.tx_renveilledocumentaire_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_veille_documentaire/pi1/class.tx_renveilledocumentaire_pi1.php']);
}

?>