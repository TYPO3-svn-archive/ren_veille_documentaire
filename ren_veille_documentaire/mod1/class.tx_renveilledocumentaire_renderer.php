<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Pierrick Caillon <pierrick@in-cite.net>
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

define('DOCUMENT_RENDERER_NO_CUSTOM_FIELDS', 1);
define('DOCUMENT_RENDERER_NO_CUSTOM_TYPES', 2);
define('DOCUMENT_RENDERER_NO_MOVE', 4);
define('DOCUMENT_RENDERER_NO_DISPLAY_CONDITION', 8);

/**
 * Helper for output rendering.
 *
 * @author Pierrick Caillon <pierrick@in-cite.net>
 * @author Mickael PAILLARD <mickael@in-cite.net>
 */
class tx_renveilledocumentaire_renderer
{
	var $mode;
	var $field_types;

	function tx_renveilledocumentaire_renderer(& $module, & $flexforms, $mode = 0)
	{
		global $TYPO3_CONF_VARS;
		$this->module = & $module;
		$this->id = $module->id;
		$this->flexforms = & $flexforms;
		$this->mode = $mode;
		$this->field_types = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid, label, conf',
			$module->tables['field_type'],
			'', '', '', '', 'uid'
		);
		$this->customtypes = array();
		$this->customtypelabels = array();
		if (isset($TYPO3_CONF_VARS['EXT_CONF']['ren_veille_documentaire']['fields']))
			foreach ($TYPO3_CONF_VARS['EXT_CONF']['ren_veille_documentaire']['fields'] as $fieldClass)
			{
				$objRef = t3lib_div::getUserObj($fieldClass, false);
				if ($objRef)
				{
					$types = $objRef->getTypes();
					foreach ($types as $typename => $typelabel)
					{
						$this->customtypes[$typename] = $objRef;
						$this->customtypelabels[$typename] = $typelabel;
					}
				}
			}
	}

	/**
	 * Displays a list-mode like record list.
	 *
	 * @param $table string The table name to show.
	 * @param $listEmpty string The text to return if the list is empty.
	 * @param $newLinks boolean Use the internal links. If not set, use db_list links.
	 * @param $paramName string The name of the param for the new and edit links.
	 * @return string The HTML-code to output.
	 */
	function showList($table, $listEmpty = '', $newLinks = false, $paramName = 'edit')
	{
		global $BE_USER,$LANG,$BACK_PATH,$CLIENT;

			// Initialize the dblist object:
		$dblist = t3lib_div::makeInstance('documentRecordList');
		$dblist->backPath = $BACK_PATH;
		$dblist->calcPerms = $BE_USER->calcPerms($this->pageinfo);
		$dblist->thumbs = 0;
		$dblist->returnUrl = $this->returnUrl;
		$dblist->allFields = 1;
		$dblist->localizationView = 0;
		$dblist->showClipboard = 1;
		$dblist->disableSingleTableView = $this->modTSconfig['properties']['disableSingleTableView'];
		$dblist->listOnlyInSingleTableMode = $this->modTSconfig['properties']['listOnlyInSingleTableView'];
		$dblist->hideTables = $this->modTSconfig['properties']['hideTables'];
		$dblist->clickTitleMode = '';
		$dblist->alternateBgColors = $this->modTSconfig['properties']['alternateBgColors']?1:0;
		$dblist->allowedNewTables = t3lib_div::trimExplode(',',$this->modTSconfig['properties']['allowedNewTables'],1);
		$dblist->newWizards = 0;
		$dblist->clickMenuEnabled = 0;
		$dblist->table = $table;
		$dblist->newLinks = $newLinks;
		$dblist->paramName = $paramName;
		$dblist->script = 'index.php';
		$dblist->filter = $this->filter;

			// Clipboard is initialized:
		$dblist->clipObj = t3lib_div::makeInstance('t3lib_clipboard');		// Start clipboard
		$dblist->clipObj->initializeClipboard();	// Initialize - reads the clipboard content from the user session
		$dblist->clipObj->backPath = $BACK_PATH;

			// Clipboard actions are handled:
		$CB = t3lib_div::_GET('CB');	// CB is the clipboard command array
		if ($this->cmd=='setCB') {
				// CBH is all the fields selected for the clipboard, CBC is the checkbox fields which were checked. By merging we get a full array of checked/unchecked elements
				// This is set to the 'el' array of the CB after being parsed so only the table in question is registered.
			$CB['el'] = $dblist->clipObj->cleanUpCBC(array_merge((array)t3lib_div::_POST('CBH'),(array)t3lib_div::_POST('CBC')),$this->cmd_table);
		}
		if (!$this->MOD_SETTINGS['clipBoard'])	$CB['setP']='normal';	// If the clipboard is NOT shown, set the pad to 'normal'.
		$dblist->clipObj->setCmd($CB);		// Execute commands.
		$dblist->clipObj->cleanCurrent();	// Clean up pad
		$dblist->clipObj->endClipboard();	// Save the clipboard content

			// This flag will prevent the clipboard panel in being shown.
			// It is set, if the clickmenu-layer is active AND the extended view is not enabled.
		$dblist->dontShowClipControlPanels = 0;

			// Deleting records...:
			// Has not to do with the clipboard but is simply the delete action. The clipboard object is used to clean up the submitted entries to only the selected table.
		if ($this->cmd=='delete')	{
			$items = $dblist->clipObj->cleanUpCBC(t3lib_div::_POST('CBC'),$this->cmd_table,1);
			if (count($items))	{
				$cmd=array();
				reset($items);
				while(list($iK)=each($items))	{
					$iKParts = explode('|',$iK);
					$cmd[$iKParts[0]][$iKParts[1]]['delete']=1;
				}
				$tce = t3lib_div::makeInstance('t3lib_TCEmain');
				$tce->stripslashes_values=0;
				$tce->start(array(),$cmd);
				$tce->process_cmdmap();

				if (isset($cmd['pages']))	{
					t3lib_BEfunc::getSetUpdateSignal('updatePageTree');
				}

				$tce->printLogErrorMessages(t3lib_div::getIndpEnv('REQUEST_URI'));
			}
		}

			// Initialize the listing object, dblist, for rendering the list:
		$this->pointer = t3lib_div::intInRange($this->module->pointer,0,100000);
		$dblist->start($this->id,$table,$this->pointer);
		$dblist->setDispFields();

			// Render the list of tables:
		$dblist->generateList();

			// Add JavaScript functions to the page:
		$JScode .= $this->module->doc->wrapScriptTags('
				function jumpExt(URL,anchor)	{	//
					var anc = anchor?anchor:"";
					window.location.href = URL+(T3_THIS_LOCATION?"&returnUrl="+T3_THIS_LOCATION:"")+anc;
					return false;
				}
				function jumpSelf(URL)	{	//
					window.location.href = URL+(T3_RETURN_URL?"&returnUrl="+T3_RETURN_URL:"");
					return false;
				}

				function setHighlight(id)	{	//
					top.fsMod.recentIds["web"]=id;
					top.fsMod.navFrameHighlightedID["web"]="pages"+id+"_"+top.fsMod.currentBank;	// For highlighting

					if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav)	{
						top.content.nav_frame.refresh_nav();
					}
				}
				'.$this->module->doc->redirectUrls().'
				'.$dblist->CBfunctions().'
				function editRecords(table,idList,addParams,CBflag)	{	//
					window.location.href="'.$BACK_PATH.'alt_doc.php?returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).
						'&edit["+table+"]["+idList+"]=edit"+addParams;
				}
				function editList(table,idList)	{	//
					var list="";

						// Checking how many is checked, how many is not
					var pointer=0;
					var pos = idList.indexOf(",");
					while (pos!=-1)	{
						if (cbValue(table+"|"+idList.substr(pointer,pos-pointer))) {
							list+=idList.substr(pointer,pos-pointer)+",";
						}
						pointer=pos+1;
						pos = idList.indexOf(",",pointer);
					}
					if (cbValue(table+"|"+idList.substr(pointer))) {
						list+=idList.substr(pointer)+",";
					}

					return list ? list : idList;
				}

				if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
			');
		if ($this->filter)
		{
			$filter = t3lib_div::_GP('filter');
			$urlparam = t3lib_div::trimExplode('&', t3lib_div::implodeArrayForUrl('', array_merge(t3lib_div::_GET(), array('filter' => array()))), true);
			$filter_html = 
				implode(
					'', 
					array_map(
						create_function(
							'$val', 
							'return \'
				<input type="hidden" name="\' . htmlspecialchars(substr($val, 0, strpos($val, \'=\'))) . \'" value="\' . htmlspecialchars(substr($val, strpos($val, \'=\') + 1)) . \'" />\';'
						),
						$urlparam
					)
				) . '
				<label for="filtertitre">'. htmlspecialchars($GLOBALS['LANG']->sL('Recherche')) .' </label><input type="text" size="40" id="filtertitre" title="' . htmlspecialchars($GLOBALS['LANG']->sL('titre')) . '" name="filter[titre]" value="' . htmlspecialchars($filter['titre']) . '" />
				<select name="filter[operator]">
					<option value="0">ET</option>
					<option value="1"'.(($filter['operator']==1)?' selected="selected"':'').'>OU</option>
					<option value="2"'.(($filter['operator']==2)?' selected="selected"':'').'>SAUF</option>
				</select>
				<br/><br/><label for="filterveille">'. htmlspecialchars($GLOBALS['LANG']->sL('veille')) .' </label><select size="1" id="filterveille" title="' . htmlspecialchars($GLOBALS['LANG']->sL('veille')) . '" name="filter[veille]">
					<option value=""></option>' .
				implode(
					'',
					array_map(
						create_function(
							'$val',
							'return \'
					<option\' . (($val[\'uid\'] == ' . intval($filter['veille']) . ') ? (\' selected="selected"\') : (\'\')) . \' value="\' . $val[\'uid\'] . \'">\' . htmlspecialchars($val[\'titre\']) . \'</option>\';'
						),
						$GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
							'uid, titre',
							'tx_renveilledocumentaire_veilles',
							'1 ' . t3lib_BEfunc::BEenableFields('tx_renveilledocumentaire_veilles') . t3lib_BEfunc::deleteClause('tx_renveilledocumentaire_veilles')
						)
					)
				) . '
				</select>
				&nbsp;<label for="filterauteur">' . htmlspecialchars($GLOBALS['LANG']->sL('auteur')) . '</label><select size="1" id="filterauteur" title="' . htmlspecialchars($GLOBALS['LANG']->sL('auteur')) . '" name="filter[auteur]">
					<option value=""></option>' .
				implode(
					'',
					array_map(
						create_function(
							'$val',
							'return \'
					<option\' . (($val[\'uid\'] == ' . intval($filter['auteur']) . ') ? (\' selected="selected"\') : (\'\')) . \' value="\' . $val[\'uid\'] . \'">\' . htmlspecialchars($val[\'nom\']) . \'</option>\';'
						),
						$GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
							'uid, nom',
							'tx_renveilledocumentaire_auteurs',
							'1 ' . t3lib_BEfunc::BEenableFields('tx_renveilledocumentaire_auteurs') . t3lib_BEfunc::deleteClause('tx_renveilledocumentaire_auteurs'),
							'',
							'nom'
						)
					)
				) . '
				</select>
				<br/><br/><label for="filterdebut">'. htmlspecialchars($GLOBALS['LANG']->sL('debut')) .' </label><input type="text" size="10" id="filterdebut" title="' . htmlspecialchars($GLOBALS['LANG']->sL('debut')) . '" name="filter[debut]" value="' . htmlspecialchars($filter['debut']) . '" />
				<label for="filterfin">'. htmlspecialchars($GLOBALS['LANG']->sL('fin')) .' </label><input type="text" size="10" id="filterfin" title="' . htmlspecialchars($GLOBALS['LANG']->sL('fin')) . '" name="filter[fin]" value="' . htmlspecialchars($filter['fin']) . '" />(JJ/MM/AAAA)
				<br/><br/>';
				
				/** CHAMPS MOT CLES DU THESAURUS **/
				$keywords = array();
				$keywords2 = array();
				$postvars = t3lib_div::_GP('data');
				if($postvars['tx_renveilledocumentaire_notices'][1]['mots_cles']){
					$data = explode(',',  $postvars['tx_renveilledocumentaire_notices'][1]['mots_cles']);
					$keys = array();
					if(is_array($data) && !empty($data)){
						foreach($data as $dat){
							$tmp = str_replace('tx_renveilledocumentaire_keywords_', '', $dat);
							if(is_numeric($tmp))
								$keys[] = $tmp;
						}
					}
					$data = implode(',', $keys);
					$keywords = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
						'uid, ' . $GLOBALS['TCA']['tx_renveilledocumentaire_keywords']['ctrl']['label'] . ' AS name',
						'tx_renveilledocumentaire_keywords',
						' uid in ('.$data.') ' .
						t3lib_BEfunc::BEenableFields('tx_renveilledocumentaire_keywords') . 
						t3lib_BEfunc::deleteClause('tx_renveilledocumentaire_keywords'),
						'',
						$GLOBALS['TCA']['tx_renveilledocumentaire_keywords']['ctrl']['label']
					);
				}
				
				$tca = array(
					'label' => $LANG->sL('LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_keywords'),
					'config' => array(
						'type' => 'group',
						'internal_type' => 'db',
						'allowed' => 'tx_renveilledocumentaire_keywords','size' => 10,
						'size' => 5,
						'autoSizeMax' => 5,
						'minitems' => 0,
						'maxitems' => 5,
						'MM' => 'tx_renveilledocumentaire_keywords_generic_mm',
						'wizards' => array(
							'suggest' => array(
							'pidList' => '###CURRENT_PID###',
							'type' => 'suggest',
							),
						),
					),
				);
									
				$row['mots_cles'] = implode(',', array_map(create_function('$v', 'return $v[\'uid\'] . \'|\' . $v[\'name\'];'), $keywords));
				$row['uid'] = 1;
				$row['pid'] = $this->module->id;
				$tceforms = t3lib_div::makeInstance('t3lib_TCEforms');
				$tceforms->initDefaultBEmode();
				$tceforms->formName = 'notice_search';
				$tceforms->docLarge = 1;
				$tceforms->disableRTE = 0;
				$tceforms->backPath = $GLOBALS['BACK_PATH'];
				$tceforms->enableClickMenu = TRUE;
				$tceforms->enableTabMenu = TRUE;
				$PA = array();
				$PA['altName'] = '';
				$PA['palette'] = 0;
				$PA['extra'] = '';
				$PA['pal'] = 0;
				$PA['fieldConf'] = $tca;
				$PA['itemFormElName'] = $tceforms->prependFormFieldNames.'[tx_renveilledocumentaire_notices]['.$row['uid'].'][mots_cles]'; // Form field name
				$PA['itemFormElName_file'] = $tceforms->prependFormFieldNames_file.'[tx_renveilledocumentaire_notices]['.$row['uid'].'][mots_cles]'; // Form field name, in case of file uploads
				$PA['itemFormElValue'] = $row['mots_cles']; // The value to show in the form field.
				$PA['itemFormElID'] = $this->_tceforms->prependFormFieldNames.'_fe_groups_'.$row['uid'].'_members';
				$PA['label'] = $tca['label'];
				$PA['fieldChangeFunc'] = array();
				$PA['fieldChangeFunc']['TBE_EDITOR_fieldChanged'] = "TBE_EDITOR.fieldChanged('tx_renveilledocumentaire_notices','".$row['uid']."','mots_cles','".$PA['itemFormElName']."');";
				$item = $tceforms->getSingleField_SW('tx_renveilledocumentaire_notices', 'mots_cles', $row, $PA);
				$out = array(
					'NAME'=>$PA['label'],
					'ITEM'=>$item,
					'TABLE'=>'tx_renveilledocumentaire_notices',
					'ID'=>$row['uid'],
					'HELP_ICON'=>'',
					'HELP_TEXT'=>'',
					'PAL_LINK_ICON'=>'',
					'FIELD'=>'mots_cles' );
				$out = $tceforms->addUserTemplateMarkers($out,'tx_renveilledocumentaire_notices','mots_cles',$row,$PA);
				// String:
				$out = $tceforms->intoTemplate($out);
				$theOutput = $tceforms->printNeededJSFunctions_top() . $out;
				$filter_html .='<select name="filter[operator_kw]"id="operator_kw">
					<option value="0">ET</option>
					<option value="1"'.(($filter['operator_kw']==1)?' selected="selected"':'').'>OU</option>
					<option value="2"'.(($filter['operator_kw']==2)?' selected="selected"':'').'>SAUF</option>
				</select>';
				$filter_html .= $theOutput;
				
						
				if($postvars['tx_renveilledocumentaire_notices'][1]['mots_cles2']){
					$data = explode(',',  $postvars['tx_renveilledocumentaire_notices'][1]['mots_cles2']);
					$keys = array();
					if(is_array($data) && !empty($data)){
						foreach($data as $dat){
							$tmp = str_replace('tx_renveilledocumentaire_keywords_', '', $dat);
							if(is_numeric($tmp))
								$keys[] = $tmp;
						}
					}
					$data = implode(',', $keys);
					$keywords2 = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
						'uid, ' . $GLOBALS['TCA']['tx_renveilledocumentaire_keywords']['ctrl']['label'] . ' AS name',
						'tx_renveilledocumentaire_keywords',
						' uid in ('.$data.') ' .
						t3lib_BEfunc::BEenableFields('tx_renveilledocumentaire_keywords') . 
						t3lib_BEfunc::deleteClause('tx_renveilledocumentaire_keywords'),
						'',
						$GLOBALS['TCA']['tx_renveilledocumentaire_keywords']['ctrl']['label']
					);
				}
				
				$tca2 = array(
					'label' => $LANG->sL('LLL:EXT:ren_veille_documentaire/locallang_db.xml:tx_renveilledocumentaire_keywords').'groupe2',
					'config' => array(
						'type' => 'group',
						'internal_type' => 'db',
						'allowed' => 'tx_renveilledocumentaire_keywords','size' => 10,
						'size' => 5,
						'autoSizeMax' => 5,
						'minitems' => 0,
						'maxitems' => 5,
						'MM' => 'tx_renveilledocumentaire_keywords_generic_mm',
						/*'wizards' => array(
							'suggest' => array(
							'pidList' => '###CURRENT_PID###',
							'type' => 'suggest',
							),
						),*/
					),
				);
									
				$row2['mots_cles'] = implode(',', array_map(create_function('$v2', 'return $v2[\'uid\'] . \'|\' . $v2[\'name\'];'), $keywords2));
				$row2['uid'] = 1;
				$row2['pid'] = $this->module->id;
				$tceforms2 = t3lib_div::makeInstance('t3lib_TCEforms');
				$tceforms2->initDefaultBEmode();
				$tceforms2->formName = 'notice_search2';
				$tceforms2->docLarge = 1;
				$tceforms2->disableRTE = 0;
				$tceforms2->backPath = $GLOBALS['BACK_PATH'];
				$tceforms2->enableClickMenu = TRUE;
				$tceforms2->enableTabMenu = TRUE;
				$PA2= array();
				$PA2['altName'] = '';
				$PA2['palette'] = 0;
				$PA2['extra'] = '';
				$PA2['pal'] = 0;
				$PA2['fieldConf'] = $tca2;
				$PA2['itemFormElName'] = $tceforms->prependFormFieldNames.'[tx_renveilledocumentaire_notices]['.$row2['uid'].'][mots_cles2]'; // Form field name
				$PA2['itemFormElName_file'] = $tceforms->prependFormFieldNames_file.'[tx_renveilledocumentaire_notices]['.$row2['uid'].'][mots_cles2]'; // Form field name, in case of file uploads
				$PA2['itemFormElValue'] = $row2['mots_cles']; // The value to show in the form field.
				$PA2['itemFormElID'] = $this->_tceforms2->prependFormFieldNames.'_fe_groups_'.$row2['uid'].'_members';
				$PA2['label'] = $tca2['label'];
				$PA2['fieldChangeFunc'] = array();
				$PA2['fieldChangeFunc']['TBE_EDITOR_fieldChanged'] = "TBE_EDITOR.fieldChanged('tx_renveilledocumentaire_notices','".$row2['uid']."','mots_cles','".$PA2['itemFormElName']."');";
				$item2 = $tceforms2->getSingleField_SW('tx_renveilledocumentaire_notices', 'mots_cles', $row2, $PA2);
				$out2 = array(
					'NAME'=>$PA2['label'],
					'ITEM'=>$item2,
					'TABLE'=>'tx_renveilledocumentaire_notices',
					'ID'=>$row2['uid'],
					'HELP_ICON'=>'',
					'HELP_TEXT'=>'',
					'PAL_LINK_ICON'=>'',
					'FIELD'=>'mots_cles2' );
				$out2 = $tceforms->addUserTemplateMarkers($out2,'tx_renveilledocumentaire_notices','mots_cles',$row2,$PA2);
				// String:
				$out2 = $tceforms2->intoTemplate($out2);
				$theOutput2 = $tceforms2->printNeededJSFunctions_top() . $out2 . $tceforms->printNeededJSFunctions();
				$filter_html .='<select name="filter[operator_kw2]" id="operator_kw2">
					<option value="0">ET</option>
					<option value="1"'.(($filter['operator_kw2']==1)?' selected="selected"':'').'>OU</option>
					<option value="2"'.(($filter['operator_kw2']==2)?' selected="selected"':'').'>SAUF</option>
				</select>';
				$filter_html .= $theOutput2;
						
				
				
				
				$filter_html .= '<br/><br/>
				<input type="submit" value="' . htmlspecialchars($GLOBALS['LANG']->getLL('applyfilter')) . '" />';
		}
		else
			$filter_html = '';
		if (empty($dblist->HTMLcode))
		{
			return ((t3lib_div::_GP('filter')) ? ($filter_html . '<br />') : ('')) . $listEmpty;
		}
		return $JScode . $filter_html . $dblist->HTMLcode;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_veille_documentaire/mod1/class.tx_renveilledocumentaire_renderer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_veille_documentaire/mod1/class.tx_renveilledocumentaire_renderer.php']);
}
