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

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Documents Watching display' for the 'ren_veille_documentaire' extension.
 *
 * @author Mickael PAILLARD <mickael@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_renveilledocumentaire
 */
class tx_renveilledocumentaire_pi1 extends tslib_pibase {
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
		$this->aTables=array();
		$this->aTables['notices']='tx_renveilledocumentaire_notices';
		$this->aTables['veilles']='tx_renveilledocumentaire_veilles';
		$this->aTables['sources']='tx_renveilledocumentaire_sources';
		$this->aTables['auteurs']='tx_renveilledocumentaire_auteurs';
		$this->aTables['mots_cles']='tx_renveilledocumentaire_keywords';
		$sContent='';
			
		if((isset($this->piVars['notice']))&&(($this->piVars['notice'])>0)&&(($this->conf['pagedetail']=='')||(!isset($this->conf['pagedetail'])))){
			$aNotices=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,veille,source,resume,auteurs,mots_cles,fichiers,url,voir_aussi,actus,titre,date', $this->aTables['notices'], 'uid='.$this->piVars['notice'].$this->cObj->enableFields($this->aTables['notices']));
			if (is_array($aNotices)){
				foreach($aNotices as $iKey=>$aNotice){
					$sLesVeilles='';
					$aSesVeilles=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('titre', $this->aTables['veilles'], 'uid IN('.$aNotice['veille'].')'.$this->cObj->enableFields($this->aTables['veilles']));
					if(is_array($aSesVeilles)){
						foreach($aSesVeilles as $iKey=>$aSaVeille){
							$sLesVeilles.=($sLesVeilles!='')?', '.$aSaVeille['titre']:$aSaVeille['titre'];
						}
					}
					$sLesSources='';
					$sLesSourcesIcon = '';
					$aImgTSConfig = $this->conf['icon.'];
					$aSesSources=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('nom, icon', $this->aTables['sources'], 'uid IN('.$aNotice['source'].')'.$this->cObj->enableFields($this->aTables['sources']));
					if(is_array($aSesSources)){
						foreach($aSesSources as $iKey=>$aSaSource){
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
					
					
					
					$sLesAuteurs='';
					$aSesAuteurs=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('nom', $this->aTables['auteurs'], 'uid IN('.$aNotice['auteurs'].')'.$this->cObj->enableFields($this->aTables['auteurs']));
					if(is_array($aSesAuteurs)){
						foreach($aSesAuteurs as $iKey=>$aSonAuteur){
							$sLesAuteurs.=($sLesAuteurs!='')?', '.$aSonAuteur['nom']:$aSonAuteur['nom'];
						}
					}
					
					$sLesMotsCles='';
					$aSesMotsCles=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('word AS mot', $this->aTables['mots_cles'], 'uid IN('.$aNotice['mots_cles'].')'.$this->cObj->enableFields($this->aTables['mots_cles']));
					if(is_array($aSesMotsCles)){
					foreach($aSesMotsCles as $iKey=>$aSonMotCle){
						$sLesMotsCles.=($sLesMotsCles!='')?', '.$aSonMotCle['mot']:$aSonMotCle['mot'];
					}
					}
					
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
					
					$sVoirAussi='';
					$aSesVoirAussi=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('titre,uid', $this->aTables['notices'], 'uid IN('.$aNotice['voir_aussi'].')'.$this->cObj->enableFields($this->aTables['notices']));
					$aLinkConfAussi=array();
					$aLinkConfAussi['parameter']=($this->conf['pagedetail']!='')?$this->conf['pagedetail']:$GLOBALS['TSFE']->id;
					if(is_array($aSesVoirAussi)){
						foreach($aSesVoirAussi as $iKey=>$aSonVoirAussi){
							$aLinkConfAussi['additionalParams']='&'.$this->prefixId.'[notice]=' . $aSonVoirAussi['uid'];
							$sVoirAussi.='<li>'.$this->cObj->typoLink($aSonVoirAussi['titre'],$aLinkConfAussi).'</li>';
						}
					}
					$sVoirAussi=($sVoirAussi!='')?'<ul class="'.$this->prefixId.'_voir_aussi">'.$sVoirAussi.'</ul>':'';
					
					$sActus='';
					$aSesActus=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('title,uid', 'tt_news', 'uid IN('.$aNotice['actus'].')'.$this->cObj->enableFields('tt_news'));
					$aLinkConfActus=array();
					$aLinkConfActus['parameter']=($this->conf['pagenews']!='')?$this->conf['pagenews']:$GLOBALS['TSFE']->id;
					if (is_array($aSesActus)){
						foreach($aSesActus as $iKey=>$sonactu){
							$aLinkConfActus['additionalParams']='&tx_ttnews[tt_news]=' . $sonactu['uid'];
							$sActus.='<li>'.$this->cObj->typoLink($sonactu['title'],$aLinkConfActus).'</li>';
						}
					}
					$sActus=($sActus!='')?'<ul class="'.$this->prefixId.'_actus">'.$sActus.'</ul>':'';
									
					$aMarkerArray=array();
					$aMarkerArray['###TITRE###']=$this->cObj->typoLink($aNotice['titre'], $aLinkConf);
					$aMarkerArray['###VEILLE###']=$sLesVeilles;
					$aMarkerArray['###VEILLE_LABEL###']=$this->pi_getLL('veille_label');
					$aMarkerArray['###SOURCE###']=$sLesSources;
					$aMarkerArray['###ICON_SOURCE###']=$sLesSourcesIcon;
					$aMarkerArray['###SOURCE_LABEL###']=$this->pi_getLL('source_label');
					$aMarkerArray['###MOTS_CLES###']=$sLesMotsCles;
					$aMarkerArray['###MOTS_CLES_LABEL###']=$this->pi_getLL('mots_cles_label');
					$aMarkerArray['###DATE###']=strftime($this->conf['dateFormat'],$aNotice['date']);
					$aMarkerArray['###DATE_LABEL###']=$this->pi_getLL('date_label');
					$aMarkerArray['###AUTEURS###']=$sLesAuteurs;
					$aMarkerArray['###AUTEURS_LABEL###']=$this->pi_getLL('auteurs_label');
					$aMarkerArray['###RESUME###']=$aNotice['resume'];
					$aMarkerArray['###RESUME_LABEL###']=$this->pi_getLL('resume_label');
					$aMarkerArray['###FICHIERS###']=$sLesFichiers;
					$aMarkerArray['###FICHIERS_LABEL###']=$this->pi_getLL('fichiers_label');
					$aMarkerArray['###URL###']=$this->cObj->typoLink($aNotice['url'],$aLienConfUrl);
					$aMarkerArray['###URL_LABEL###']=($aMarkerArray['###URL###']!='')?$this->pi_getLL('url_label'):'';
					$aMarkerArray['###VOIR_AUSSI###']=$sVoirAussi;
					$aMarkerArray['###VOIR_AUSSI_LABEL###']=($aMarkerArray['###VOIR_AUSSI###']!='')?'<h2><span>'.$this->pi_getLL('voir_aussi_label').'</span></h2>':'';
					$aMarkerArray['###ACTUS###']=$sActus;
					$aMarkerArray['###ACTUS_LABEL###']=($aMarkerArray['###ACTUS###']!='')?'<h2><span>'.$this->pi_getLL('actus_label').'</span></h2>':'';
					
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalNoticeSingleFields'])) {
						foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalNoticeSingleFields'] as $_classRef) {
							$_procObj = & t3lib_div::getUserObj($_classRef);
							$_procObj->additionalNoticeSingleFields($aMarkerArray, $aNotice, $this->conf, $this);
						}
					}
		
					$sContent.= $this->viewTemplate('###TEMPLATE_DETAIL###',$aMarkerArray);
				}
			}
		}
		else{
			$sWhere='';
			$iPiVarsVeille=0;
			if((isset($this->piVars['veille']))&&($this->piVars['veille']>0)) $iPiVarsVeille=$this->piVars['veille'];
			if($this->conf['veilles']!=''){
				$aVeillesAffichees=array();
				$aVeilles=explode(',',$this->conf['veilles']);
				$aVeillesAffichees=($iPiVarsVeille>0) ? array(0=>$iPiVarsVeille):$aVeilles;
		
				if(is_array($aVeillesAffichees)){
				foreach ($aVeillesAffichees as $iKey=>$iVeilleUid){
					$sOp=((isset($this->conf['operateur']))&&($this->conf['operateur']>0))?' AND ':' OR ';
					$sWhere.=($sWhere=='')?'':$sOp;
					$sWhere.='(veille='.$iVeilleUid.' OR veille LIKE \''.$iVeilleUid.',%\' OR veille LIKE \'%,'.$iVeilleUid.',%\' OR veille LIKE \'%,'.$iVeilleUid.'\')';
				}
				}
				if ((count($aVeilles))>1){
					$sVeilleSelect='
					<form action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).'" method="post">
						<fieldset>
							<select name="'.$this->prefixId.'[veille]">
								<option></option>';
						foreach ($aVeilles as $iKey=>$iVeilleUid){
							$aRVeilles=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,titre', $this->aTables['veilles'], 'uid='.$iVeilleUid.$this->cObj->enableFields($this->aTables['veilles']), '', 'titre');
							foreach($aRVeilles as $iKey=>$aOptVeille){
								$sSelected=($aOptveille['uid']==$iPiVarsVeille)?' selected="selected"':'';
								$sVeilleSelect.='
								<option value="'.$aOptVeille['uid'].'"'.$sSelected.'>'.$aOptVeille['titre'].'</option>';
							}
						}
					$sVeilleSelect.='
							</select>
							<input type="submit" value="OK" class="submit"/>
						</fieldset>
					</form>
					';
				}
				else $sVeilleSelect='';
			}
			else{
				$sVeilleSelect='
				<form action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).'" method="post">
					<fieldset>
						<select name="'.$this->prefixId.'[veille]">
							<option></option>';
					
						$aRVeilles=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,titre', $this->aTables['veilles'], $this->cObj->enableFields($this->aTables['veilles']), '', 'titre');
						if(is_array($aRVeilles)){
						foreach($aRVeilles as $iKey=>$aOptVeille){
							$sSelected=($aOptVeille['uid']==$iPiVarsVeille)?' selected="selected"':'';
							$sVeilleSelect.='
							<option value="'.$aOptVeille['uid'].'"'.$sSelected.'>'.$aOptVeille['titre'].'</option>';
						}
						}
					
				$sVeilleSelect.='
						</select>
						<input type="submit" value="OK" class="submit"/>
					</fieldset>
				</form>
				';
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
			$sSelect = 'tx_renveilledocumentaire_notices.veille,tx_renveilledocumentaire_notices.uid,tx_renveilledocumentaire_notices.titre,tx_renveilledocumentaire_notices.date, tx_renveilledocumentaire_sources.icon';
			$sFrom = $this->aTables['notices'].' INNER JOIN tx_renveilledocumentaire_sources ON tx_renveilledocumentaire_notices.source = tx_renveilledocumentaire_sources.uid';
			
			$aNotices=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows($sSelect, $sFrom, $sWhere.$this->cObj->enableFields($this->aTables['notices']), '', 'date DESC', $sLimit);
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
						'additionalParams' => '&'.$this->prefixId.'[notice]=' . $aNotice['uid'],
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
					$aMarkerArray['###VEILLE###']=((isset($this->conf['operateur']))&&($this->conf['operateur']>0))?'':$sLesVeilles;
					
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalNoticeSingleFields'])) {
						foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalNoticeSingleFields'] as $_classRef) {
							$_procObj = & t3lib_div::getUserObj($_classRef);
							$_procObj->additionalNoticeSingleFields($aMarkerArray, $aNotice, $this->conf, $this);
						}
					}
					
					$sListe.= $this->viewTemplate('###TEMPLATE_ITEM###',$aMarkerArray);
				}
			}
			$sListe=($sListe!='')?'<ul class="'.$this->prefixId.'_liste">'.$sListe.'</ul>':'';
			
			$aMarkerArray=array();
				$aMarkerArray['###VEILLE_SELECT###']=((isset($this->conf['operateur']))&&($this->conf['operateur']>0))?'':$sVeilleSelect;
				$aMarkerArray['###NAV_PAGES###']=$sPagesLinks;
				$aMarkerArray['###LISTE###']=$sListe;
				
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalListFields'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalListFields'] as $_classRef) {
					$_procObj = & t3lib_div::getUserObj($_classRef);
					$_procObj->additionalListFields($aMarkerArray, $this->conf, $this);
				}
			}
					
			$sContent.= $this->viewTemplate('###TEMPLATE_LIST###',$aMarkerArray);
		}
		return $this->pi_wrapInBaseClass($sContent);
	}
	function init()
	{
		$this->pi_initPIflexForm();
		$aPiFlexForm = $this->cObj->data['pi_flexform'];
		// Traverse the entire array based on the language...
		// and assign each configuration option to $this->conf array...
		if (is_array($aPiFlexForm)){
			foreach ( $aPiFlexForm['data'] as $sheet => $data )
			foreach ( $data as $lang => $value )
			foreach ( $value as $iKey => $val ){
				if($this->pi_getFFvalue($aPiFlexForm, $iKey, $sheet)!=''){
					$this->conf[$iKey] = $this->pi_getFFvalue($aPiFlexForm, $iKey, $sheet);
				}
			}
		}
		if(empty($this->conf))
			$this->conf = array();
		
		if(isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]) && 
		!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]))
			$this->conf = array_merge(unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]),$this->conf);
		
		$this->template = $this->getTemplateFile('');
		//echo $this->template;
		$this->incCssFile(t3lib_extMgm::siteRelPath($this->extKey) . 'res/css/default.css');
		
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['initConfiguration'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['initConfiguration'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$_procObj->initConfiguration($this->conf, $this);
			}
		}
	}
	/**
	 * Get subpart of template and replace values with markers array
	 *
	 * @param	string		Subtemplate name
	 * @param	array		markers/values
	 * @return	subtemplate HTML
	 */
	function viewTemplate($nametemplate, $markers){
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->setParent($this->cObj->data,$this->cObj->currentRecord);
		
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalTemplateFields'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalTemplateFields'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$_procObj->additionalTemplateFields($nametemplate, $markers, $this->conf, $this);
			}
		}
			
		$templatename = basename($this->template);
        $sContent = $cObj->getSubpart(file_get_contents($this->template), $nametemplate);
		$sContent = $cObj->substituteMarkerArray($sContent, $markers);
		return $sContent;
	}
	
	
	 /**
	 * Retrieve template file name.
	 *
	 * @param $mode string The rendering mode.
	 * @return string The template filename and path.
	 */
	function getTemplateFile($mode)
	{
		$template = '';
		$templates = $this->getTemplateFiles($mode);
		if (!empty($templates))
			$template = $templates[0];
		return $template;
	}
	
    /**
	 * Retrieve available template file names.
	 *
	 * @param $mode string The rendering mode.
	 * @return array All available template filename <ith full path.
	 */
	function getTemplateFiles($mode)
	{
		$templates = array();
		if (isset($this->conf['templatePath']) && is_dir(t3lib_div::getFileAbsFileName($this->conf['templatePath'])))
		{
			if (isset($this->conf['template']) && is_file($this->conf['template']))
			{
				$templates[] = t3lib_div::getFileAbsFileName($this->conf['template']);
			}
			if (isset($this->conf['defaultTemplate']) && is_file(t3lib_div::getFileAbsFileName($this->conf['templatePath']) . $this->conf['defaultTemplate']))
			{
				$templates[] = t3lib_div::getFileAbsFileName($this->conf['templatePath']) . $this->conf['defaultTemplate'];
			}
		}
		if (isset($this->conf[$mode]['templatePath']) && is_dir(t3lib_div::getFileAbsFileName($this->conf[$mode]['templatePath'])))
		{
			if (isset($this->conf[$mode]['template']) && is_file(t3lib_div::getFileAbsFileName($this->conf[$mode]['templatePath']) . $this->conf[$mode]['template']))
			{
				$templates[] = t3lib_div::getFileAbsFileName($this->conf[$mode]['templatePath']) . $this->conf[$mode]['template'];
			}
			if (isset($this->conf[$mode]['defaultTemplate']) && is_file(t3lib_div::getFileAbsFileName($this->conf[$mode]['templatePath']) . $this->conf[$mode]['defaultTemplate']))
			{
				$templates[] = t3lib_div::getFileAbsFileName($this->conf[$mode]['templatePath']) . $this->conf[$mode]['defaultTemplate'];
			}
		}
		array_reverse($templates);
		// TODO: plugin configuration take precedence.
		return $templates;
	}
	
	
	/**
	* Function to insert Javascript at Ext. Runtime
	*
	* @param string $script Input the Script Name to insert JS
	* @return
	*/
	
	function incJsFile($script,$jsCode = false) {
		if(!$jsCode)
			$js = '<script src="'.$script.'" type="text/javascript"><!-- //--></script>';
		else
		{
			$js .= '<script type="text/javascript">
				'.$script.'
			</script>';
		}
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= $js;
	}
	
	/**
	* Function to insert CSS
	*
	* @param string $cssFile Input the Css Name to insert JS
	* @return
	*/
	
	function incCssFile($cssFile) {
		$css = '<link type="text/css" href="' . $cssFile . '" rel="stylesheet" />';
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= $css;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_veille_documentaire/pi1/class.tx_renveilledocumentaire_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_veille_documentaire/pi1/class.tx_renveilledocumentaire_pi1.php']);
}

?>

