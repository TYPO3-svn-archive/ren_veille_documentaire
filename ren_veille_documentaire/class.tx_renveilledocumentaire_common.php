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

require_once(PATH_tslib.'class.tslib_pibase.php');

/** 
 * Plugins Commons functions
 *
 * @author Emilie PRUD'HOMME <emilie@in-cite.net>
 * @author Mickaël PAILLARD <mickael@in-cite.net>
 */
class tx_renveilledocumentaire_common extends tslib_pibase 
{
	
	function init() {
		$this->aTables=array();
		$this->aTables['notices']='tx_renveilledocumentaire_notices';
		$this->aTables['veilles']='tx_renveilledocumentaire_veilles';
		$this->aTables['sources']='tx_renveilledocumentaire_sources';
		$this->aTables['auteurs']='tx_renveilledocumentaire_auteurs';
		$this->aTables['mots_cles']='tx_renveilledocumentaire_keywords';
		
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
	function viewTemplate($nametemplate, $markers = array(), $subparts = array()){
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
		
		if (!empty($markers) || !empty($subparts))
			$sContent = $cObj->substituteMarkerArrayCached($sContent, $markers, $subparts);
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