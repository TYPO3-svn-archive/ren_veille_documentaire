<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Pierrick Caillon fo In Cite Solution <pierrick@in-cite.net)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_t3lib . 'class.t3lib_recordlist.php');
require_once(PATH_typo3 . 'class.db_list.inc');
require_once(PATH_typo3 . 'class.db_list_extra.inc');

class documentRecordList extends localRecordList
{
	/**
	 * Creates the control panel for a single record in the listing.
	 *
	 * @param	string		The table
	 * @param	array		The record for which to make the control panel.
	 * @return	string		HTML table with the control panel (unless disabled)
	 */
	function makeControl($table,$row)	{
		global $TCA, $LANG, $SOBE, $TYPO3_CONF_VARS;
		if ($this->dontShowClipControlPanels)	return '';

			// Initialize:
		t3lib_div::loadTCA($table);
		$cells=array();

			// If the listed table is 'pages' we have to request the permission settings for each page:
		if ($table=='pages')	{
			$localCalcPerms = $GLOBALS['BE_USER']->calcPerms(t3lib_BEfunc::getRecord('pages',$row['uid']));
		}

			// This expresses the edit permissions for this particular element:
		$permsEdit = ($table=='pages' && ($localCalcPerms&2)) || ($table!='pages' && ($this->calcPerms&16));

			// "Show" link (only pages and tt_content elements)
		if ($table=='pages' || $table=='tt_content')	{
			$params='&edit['.$table.']['.$row['uid'].']=edit';
			$cells['view']='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($table=='tt_content'?$this->id.'#'.$row['uid']:$row['uid'], $this->backPath)).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/zoom.gif','width="12" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.showPage',1).'" alt="" />'.
					'</a>';
		} elseif(!$this->table) {
			$cells['view'] = $this->spaceIcon;
		}

			// "Edit" link: ( Only if permissions to edit the page-record of the content of the parent page ($this->id)
		if ($permsEdit)	{
			$params='&edit['.$table.']['.$row['uid'].']=edit';
			$cells['edit']='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,$this->returnUrl)).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/edit2'.(!$TCA[$table]['ctrl']['readOnly']?'':'_d').'.gif','width="11" height="12"').' title="'.$LANG->getLL('edit',1).'" alt="" />'.
					'</a>';
		} elseif(!$this->table) {
			$cells['edit'] = $this->spaceIcon;
		}

			// "Move" wizard link for pages/tt_content elements:
		if (($table=="tt_content" && $permsEdit) || ($table=='pages'))	{
			$cells['move']='<a href="#" onclick="'.htmlspecialchars('return jumpExt(\''.$this->backPath.'move_el.php?table='.$table.'&uid='.$row['uid'].'\');').'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/move_'.($table=='tt_content'?'record':'page').'.gif','width="11" height="12"').' title="'.$LANG->getLL('move_'.($table=='tt_content'?'record':'page'),1).'" alt="" />'.
					'</a>';
		} elseif(!$this->table) {
			$cells['move'] = $this->spaceIcon;
		}

			// If the extended control panel is enabled OR if we are seeing a single table:
		if ($SOBE->MOD_SETTINGS['bigControlPanel'] || $this->table)	{

				// "Info": (All records)
			$cells['viewBig']='<a href="#" onclick="'.htmlspecialchars('top.launchView(\''.$table.'\', \''.$row['uid'].'\'); return false;').'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/zoom2.gif','width="12" height="12"').' title="'.$LANG->getLL('showInfo',1).'" alt="" />'.
					'</a>';

				// If the table is NOT a read-only table, then show these links:
			if (!$TCA[$table]['ctrl']['readOnly'])	{

					// "Revert" link (history/undo)
				$cells['history']='<a href="#" onclick="'.htmlspecialchars('return jumpExt(\''.$this->backPath.'show_rechis.php?element='.rawurlencode($table.':'.$row['uid']).'\',\'#latest\');').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/history2.gif','width="13" height="12"').' title="'.$LANG->getLL('history',1).'" alt="" />'.
						'</a>';

					// Versioning:
				if (t3lib_extMgm::isLoaded('version'))	{
					$vers = t3lib_BEfunc::selectVersionsOfRecord($table, $row['uid'], 'uid', $GLOBALS['BE_USER']->workspace, FALSE, $row);
					if (is_array($vers))	{	// If table can be versionized.
						if (count($vers)>1)	{
							$class = 'typo3-ctrl-versioning-multipleVersions';
							$lab = count($vers)-1;
						} else {
							$class = 'typo3-ctrl-versioning-oneVersion';
							$lab = 'V';
						}

						$cells['version']='<a href="'.htmlspecialchars($this->backPath.t3lib_extMgm::extRelPath('version').'cm1/index.php?table='.rawurlencode($table).'&uid='.rawurlencode($row['uid'])).'" title="'.$LANG->getLL('displayVersions',1).'" class="typo3-ctrl-versioning ' . $class . '">'.
								$lab.
								'</a>';
					} elseif(!$this->table) {
						$cells['version'] = '<span class="typo3-ctrl-versioning typo3-ctrl-versioning-hidden">V</span>';
					}
				}

					// "Edit Perms" link:
				if ($table=='pages' && $GLOBALS['BE_USER']->check('modules','web_perm'))	{
					$cells['perms']='<a href="'.htmlspecialchars('mod/web/perm/index.php?id='.$row['uid'].'&return_id='.$row['uid'].'&edit=1').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/perm.gif','width="7" height="12"').' title="'.$LANG->getLL('permissions',1).'" alt="" />'.
							'</a>';
				} elseif(!$this->table && $GLOBALS['BE_USER']->check('modules','web_perm')) {
					$cells['perms'] = $this->spaceIcon;
				}

					// "New record after" link (ONLY if the records in the table are sorted by a "sortby"-row or if default values can depend on previous record):
				if ($TCA[$table]['ctrl']['sortby'] || $TCA[$table]['ctrl']['useColumnsForDefaultValues'])	{
					if (
						($table!='pages' && ($this->calcPerms&16)) || 	// For NON-pages, must have permission to edit content on this parent page
						($table=='pages' && ($this->calcPerms&8))		// For pages, must have permission to create new pages here.
						)	{
						if ($this->showNewRecLink($table))	{
							$params='&edit['.$table.']['.(-($row['_MOVE_PLH']?$row['_MOVE_PLH_uid']:$row['uid'])).']=new';
							$cells['new']='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,$this->returnUrl)).'">'.
									'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$LANG->getLL('new'.($table=='pages'?'Page':'Record'),1).'" alt="" />'.
									'</a>';
						}
					}
				} elseif(!$this->table) {
					$cells['new'] = $this->spaceIcon;
				}

					// "Up/Down" links
				if ($permsEdit && $TCA[$table]['ctrl']['sortby']  && !$this->sortField && !$this->searchLevels)	{
					if (isset($this->currentTable['prev'][$row['uid']]))	{	// Up
						$params='&cmd['.$table.']['.$row['uid'].'][move]='.$this->currentTable['prev'][$row['uid']];
						$cells['moveUp']='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_up.gif','width="11" height="10"').' title="'.$LANG->getLL('moveUp',1).'" alt="" />'.
								'</a>';
					} else {
						$cells['moveUp'] = $this->spaceIcon;
					}
					if ($this->currentTable['next'][$row['uid']])	{	// Down
						$params='&cmd['.$table.']['.$row['uid'].'][move]='.$this->currentTable['next'][$row['uid']];
						$cells['moveDown']='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_down.gif','width="11" height="10"').' title="'.$LANG->getLL('moveDown',1).'" alt="" />'.
								'</a>';
					} else {
						$cells['moveDown'] = $this->spaceIcon;
					}
				} elseif(!$this->table) {
					$cells['moveUp']  = $this->spaceIcon;
					$cells['moveDown'] = $this->spaceIcon;
				}

					// "Hide/Unhide" links:
				$hiddenField = $TCA[$table]['ctrl']['enablecolumns']['disabled'];
				if ($permsEdit && $hiddenField && $TCA[$table]['columns'][$hiddenField] && (!$TCA[$table]['columns'][$hiddenField]['exclude'] || $GLOBALS['BE_USER']->check('non_exclude_fields',$table.':'.$hiddenField)))	{
					if ($row[$hiddenField])	{
						$params='&data['.$table.']['.$row['uid'].']['.$hiddenField.']=0';
						$cells['hide']='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_unhide.gif','width="11" height="10"').' title="'.$LANG->getLL('unHide'.($table=='pages'?'Page':''),1).'" alt="" />'.
								'</a>';
					} else {
						$params='&data['.$table.']['.$row['uid'].']['.$hiddenField.']=1';
						$cells['hide']='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_hide.gif','width="11" height="10"').' title="'.$LANG->getLL('hide'.($table=='pages'?'Page':''),1).'" alt="" />'.
								'</a>';
					}
				} elseif(!$this->table) {
					$cells['hide'] = $this->spaceIcon;
				}

					// "Delete" link:
				if (($table=='pages' && ($localCalcPerms&4)) || ($table!='pages' && ($this->calcPerms&16))) {
					$titleOrig = t3lib_BEfunc::getRecordTitle($table,$row,FALSE,TRUE);
					$title = t3lib_div::slashJS(t3lib_div::fixed_lgd_cs($titleOrig, $this->fixedL), 1);
					$params = '&cmd['.$table.']['.$row['uid'].'][delete]=1';

					$refCountMsg = t3lib_BEfunc::referenceCount($table, $row['uid'], ' ' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.referencesToRecord'), count($this->references)) .
						t3lib_BEfunc::translationCount($table, $row['uid'], ' ' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.translationsOfRecord'));
					$cells['delete']='<a href="#" onclick="'.htmlspecialchars('if (confirm('.$LANG->JScharCode($LANG->getLL('deleteWarning').' "'. $title.'" '.$refCountMsg).')) {jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');} return false;').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('delete',1).'" alt="" />'.
							'</a>';
				} elseif(!$this->table) {
					$cells['delete'] = $this->spaceIcon;
				}

					// "Levels" links: Moving pages into new levels...
				if ($permsEdit && $table=='pages' && !$this->searchLevels)	{

						// Up (Paste as the page right after the current parent page)
					if ($this->calcPerms&8)	{
						$params='&cmd['.$table.']['.$row['uid'].'][move]='.-$this->id;
						$cells['moveLeft']='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_left.gif','width="11" height="10"').' title="'.$LANG->getLL('prevLevel',1).'" alt="" />'.
								'</a>';
					}
						// Down (Paste as subpage to the page right above)
					if ($this->currentTable['prevUid'][$row['uid']])	{
						$localCalcPerms = $GLOBALS['BE_USER']->calcPerms(t3lib_BEfunc::getRecord('pages',$this->currentTable['prevUid'][$row['uid']]));
						if ($localCalcPerms&8)	{
							$params='&cmd['.$table.']['.$row['uid'].'][move]='.$this->currentTable['prevUid'][$row['uid']];
							$cells['moveRight']='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
									'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_right.gif','width="11" height="10"').' title="'.$LANG->getLL('nextLevel',1).'" alt="" />'.
									'</a>';
						} else {
							$cells['moveRight'] = $this->spaceIcon;
						}
					} else {
						$cells['moveRight'] = $this->spaceIcon;
					}
				} elseif(!$this->table) {
					$cells['moveLeft'] = $this->spaceIcon;
					$cells['moveRight'] = $this->spaceIcon;
				}
			}
		}


		/**
		 * @hook			recStatInfoHooks: Allows to insert HTML before record icons on various places
		 * @date			2007-09-22
		 * @request		Kasper Skaarhoj  <kasper2007@typo3.com>
		 */
		if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']))	{
			$stat='';
			$_params = array($table,$row['uid']);
			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['recStatInfoHooks'] as $_funcRef)	{
				$stat.=t3lib_div::callUserFunction($_funcRef,$_params,$this);
			}
			$cells['stat'] = $stat;
		}
		/**
		 * @hook			makeControl: Allows to change control icons of records in list-module
		 * @date			2007-11-20
		 * @request		Bernhard Kraft  <krafbt@kraftb.at>
		 * @usage		This hook method gets passed the current $cells array as third parameter. This array contains values for the icons/actions generated for each record in Web>List. Each array entry is accessible by an index-key. The order of the icons is dependend on the order of those array entries.
		 */
		if(is_array($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions'])) {
			foreach($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions'] as $classData) {
				$hookObject = t3lib_div::getUserObj($classData);
				if(!($hookObject instanceof localRecordList_actionsHook))	{
					throw new UnexpectedValueException('$hookObject must implement interface localRecordList_actionsHook', 1195567840);
				}
				$cells = $hookObject->makeControl($table, $row, $cells, $this);
			}
		}

			// Compile items into a DIV-element:
		return '
											<!-- CONTROL PANEL: '.$table.':'.$row['uid'].' -->
											<div class="typo3-DBctrl">'.implode('',$cells).'</div>';
	}

	function addSortLink($code,$field,$table)
	{
		// Certain circumstances just return string right away (no links):
		if ($field=='_CONTROL_' || $field=='_LOCALIZATION_' || $field=='_CLIPBOARD_' || $field=='_REF_' || $this->disableSingleTableView)
			return $code;

		// If "_PATH_" (showing record path) is selected, force sorting by pid field (will at least group the records!)
		if ($field=='_PATH_')
			$field = 'pid';

		//       Create the sort link:
		//$sortUrl = $this->listURL('',-1,'sortField,sortRev,table').'&table='.$table.'&sortField='.$field.'&sortRev='.($this->sortRev || ($this->sortField!=$field)?0:1);
		$sortUrl = t3lib_div::linkThisScript(array('table' => $table, 'sortField' => $field, 'sortRev' => ($this->sortRev || ($this->sortField!=$field)?0:1), 'filter' => t3lib_div::_GP('filter')));
		$sortArrow = ($this->sortField==$field?'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/red'.($this->sortRev?'up':'down').'.gif','width="7" height="4"').' alt="" />':'');

		// Return linked field:
		return '<a href="'.htmlspecialchars($sortUrl).'">'.$code.
			$sortArrow.
			'</a>';
	}

	/**
	 * Creates the listing of records from a single table
	 *
	 * @param	string		Table name
	 * @param	integer		Page id
	 * @param	string		List of fields to show in the listing. Pseudo fields will be added including the record header.
	 * @return	string		HTML table with the listing for the record.
	 */
	function getTable($table,$id,$rowlist)	{
		global $TCA;

			// Loading all TCA details for this table:
		t3lib_div::loadTCA($table);

			// Init
		$addWhere = '';
		$titleCol = $TCA[$table]['ctrl']['label'];
		$thumbsCol = $TCA[$table]['ctrl']['thumbnail'];
		$l10nEnabled = $TCA[$table]['ctrl']['languageField'] && $TCA[$table]['ctrl']['transOrigPointerField'] && !$TCA[$table]['ctrl']['transOrigPointerTable'];

			// Cleaning rowlist for duplicates and place the $titleCol as the first column always!
		$this->fieldArray=array();
		$this->fieldArray[] = $titleCol;	// Add title column
		if ($this->localizationView && $l10nEnabled)	{
			$this->fieldArray[] = '_LOCALIZATION_';
			$this->fieldArray[] = '_LOCALIZATION_b';
			$addWhere.=' AND (
				'.$TCA[$table]['ctrl']['languageField'].'<=0
				OR 
				'.$TCA[$table]['ctrl']['transOrigPointerField'].' = 0
			)';
		}
		
		if ($this->filter)
		{
			$filter = t3lib_div::_GP('filter');
			if (isset($filter['titre']) && !empty($filter['titre'])){
				$op='AND ';
				if (isset($filter['operator']) && ($filter['operator']>0)){
					switch($filter['operator'])	{
						case 1:			
							$op='OR ';
						break;
						case 2:			
							$op='AND !';
						break;
						default:
							$op='AND ';
						break;
					}
				}
				$searchedWords=explode(' ',$filter['titre']);
				$whereQuery='';
				foreach($searchedWords as $searchedWord){
					$whereQuery .= ($whereQuery!='')?' '.$op:'';
					$whereQuery .= (($whereQuery=='')&&($op=='AND !'))?'!':'';
					$whereQuery.='(`' . $table . '`.`titre` LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr('%' .$GLOBALS['TYPO3_DB']->escapeStrForLike($searchedWord, $table) . '%', $table).
				' OR `' . $table . '`.`resume` LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr('%' . $GLOBALS['TYPO3_DB']->escapeStrForLike($searchedWord, $table) . '%', $table).')';
				}
				$addWhere.=($whereQuery!='')?' AND ('.$whereQuery.')':'';
			}
			if (isset($filter['veille']) && is_numeric($filter['veille']))
				$addWhere .= ' AND (`' . $table . '`.`veille` = ' . $filter['veille'].' OR `' . $table . '`.`veille` LIKE \'' . $filter['veille'].',%\'  OR `' . $table . '`.`veille` LIKE \'%,' . $filter['veille'].',%\' OR `' . $table . '`.`veille` LIKE \'%,' . $filter['veille'].'\')';
			if (isset($filter['auteur']) && is_numeric($filter['auteur']))
				$addWhere .= ' AND (`' . $table . '`.`auteurs` = ' . $filter['auteur'].' OR `' . $table . '`.`auteurs` LIKE \'' . $filter['auteur'].',%\'  OR `' . $table . '`.`auteurs` LIKE \'%,' . $filter['auteur'].',%\' OR `' . $table . '`.`auteurs` LIKE \'%,' . $filter['auteur'].'\')';
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
			
;			$postvars = t3lib_div::_GP('data');
			if($postvars['tx_renveilledocumentaire_notices'][1]['mots_cles']){
				$data = explode(',',  $postvars['tx_renveilledocumentaire_notices'][1]['mots_cles']);
				if(is_array($data) && !empty($data)){
					$opkw='AND ';
					if (isset($filter['operator_kw']) && ($filter['operator_kw']>0)){
						switch($filter['operator_kw'])	{
							case 1:			
								$opkw='OR ';
							break;
							case 2:			
								$opkw='AND !';
							break;
							default:
								$opkw='AND ';
							break;
						}
					}
					$whereQuery='';
					foreach($data as $dat){
						$tmp = str_replace('tx_renveilledocumentaire_keywords_', '', $dat);
						if(is_numeric($tmp)){
							$whereQuery .= ($whereQuery!='')?' '.$opkw:'';
							$whereQuery .= (($whereQuery=='')&&($opkw=='AND !'))?'!':'';
							$whereQuery .= '(`' . $table . '`.`mots_cles` = ' . $tmp.' OR `' . $table . '`.`mots_cles` LIKE \'' . $tmp.',%\'  OR `' . $table . '`.`mots_cles` LIKE \'%,' . $tmp.',%\' OR `' . $table . '`.`mots_cles` LIKE \'%,' . $tmp.'\')';
						}
					}
					$addWhere.=($whereQuery!='')?' AND ('.$whereQuery.')':'';
					if($postvars['tx_renveilledocumentaire_notices'][1]['mots_cles2']){
						$data2 = explode(',',  $postvars['tx_renveilledocumentaire_notices'][1]['mots_cles2']);
						$opkw2='AND ';
						if (isset($filter['operator_kw2']) && ($filter['operator_kw2']>0)){
							switch($filter['operator_kw2'])	{
								case 1:			
									$opkw2='OR ';
								break;
								case 2:			
									$opkw2='AND !';
								break;
								default:
									$opkw2='AND ';
								break;
							}
						}
						
						$whereQuery='';
						foreach($data2 as $dat2){
							$tmp2 = str_replace('tx_renveilledocumentaire_keywords_', '', $dat2);
							if(is_numeric($tmp2)){
								$whereQuery .= ($whereQuery!='')?' '.$opkw2:'';
								$whereQuery .= (($whereQuery=='')&&($opkw2=='AND !'))?'!':'';
								$whereQuery .= '(`' . $table . '`.`mots_cles` = ' . $tmp2.' OR `' . $table . '`.`mots_cles` LIKE \'' . $tmp2.',%\'  OR `' . $table . '`.`mots_cles` LIKE \'%,' . $tmp2.',%\' OR `' . $table . '`.`mots_cles` LIKE \'%,' . $tmp2.'\')';
							}
						}
						$addWhere.=($whereQuery!='')?' AND ('.$whereQuery.')':'';
						var_dump($addWhere);
					}
				}
			}
		}
		
		if (!t3lib_div::inList($rowlist,'_CONTROL_'))	{
			$this->fieldArray[] = '_CONTROL_';
		}
		if ($this->showClipboard)	{
			$this->fieldArray[] = '_CLIPBOARD_';
		}
		if (!$this->dontShowClipControlPanels)	{
			$this->fieldArray[]='_REF_';
		}
		if ($this->searchLevels)	{
			$this->fieldArray[]='_PATH_';
		}
			// Cleaning up:
		$this->fieldArray=array_unique(array_merge($this->fieldArray,t3lib_div::trimExplode(',',$rowlist,1)));
		if ($this->noControlPanels)	{
			$tempArray = array_flip($this->fieldArray);
			unset($tempArray['_CONTROL_']);
			unset($tempArray['_CLIPBOARD_']);
			$this->fieldArray = array_keys($tempArray);
		}

			// Creating the list of fields to include in the SQL query:
		$selectFields = $this->fieldArray;
		$selectFields[] = 'uid';
		$selectFields[] = 'pid';
		if ($thumbsCol)	$selectFields[] = $thumbsCol;	// adding column for thumbnails
		if ($table=='pages')	{
			if (t3lib_extMgm::isLoaded('cms'))	{
				$selectFields[] = 'module';
				$selectFields[] = 'extendToSubpages';
			}
			$selectFields[] = 'doktype';
		}
		if (is_array($TCA[$table]['ctrl']['enablecolumns']))	{
			$selectFields = array_merge($selectFields,$TCA[$table]['ctrl']['enablecolumns']);
		}
		if ($TCA[$table]['ctrl']['type'])	{
			$selectFields[] = $TCA[$table]['ctrl']['type'];
		}
		if ($TCA[$table]['ctrl']['typeicon_column'])	{
			$selectFields[] = $TCA[$table]['ctrl']['typeicon_column'];
		}
		if ($TCA[$table]['ctrl']['versioningWS'])	{
			$selectFields[] = 't3ver_id';
			$selectFields[] = 't3ver_state';
			$selectFields[] = 't3ver_wsid';
			$selectFields[] = 't3ver_swapmode';		// Filtered out when pages in makeFieldList()
		}
		if ($l10nEnabled)	{
			$selectFields[] = $TCA[$table]['ctrl']['languageField'];
			$selectFields[] = $TCA[$table]['ctrl']['transOrigPointerField'];
		}
		if ($TCA[$table]['ctrl']['label_alt'])	{
			$selectFields = array_merge($selectFields,t3lib_div::trimExplode(',',$TCA[$table]['ctrl']['label_alt'],1));
		}
		$selectFields = array_unique($selectFields);		// Unique list!
		$selectFields = array_intersect($selectFields,$this->makeFieldList($table,1));		// Making sure that the fields in the field-list ARE in the field-list from TCA!
		$selFieldList = implode(',',$selectFields);		// implode it into a list of fields for the SQL-statement.

			// Create the SQL query for selecting the elements in the listing:
		$queryParts = $this->makeQueryArray($table, $id,$addWhere,$selFieldList);	// (API function from class.db_list.inc)
		$this->setTotalItems($queryParts);		// Finding the total amount of records on the page (API function from class.db_list.inc)

			// Init:
		$dbCount = 0;
		$out = '';

			// If the count query returned any number of records, we perform the real query, selecting records.
		if ($this->totalItems)	{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);
			$dbCount = $GLOBALS['TYPO3_DB']->sql_num_rows($result);
		}

		$LOISmode = $this->listOnlyInSingleTableMode && !$this->table;

			// If any records was selected, render the list:
		if ($dbCount)	{

				// Half line is drawn between tables:
			if (!$LOISmode)	{
				$theData = Array();
				if (!$this->table && !$rowlist)	{
					$theData[$titleCol] = '<img src="clear.gif" width="'.($GLOBALS['SOBE']->MOD_SETTINGS['bigControlPanel']?'230':'350').'" height="1" alt="" />';
					if (in_array('_CONTROL_',$this->fieldArray))	$theData['_CONTROL_']='';
					if (in_array('_CLIPBOARD_',$this->fieldArray))	$theData['_CLIPBOARD_']='';
				}
				$out.=$this->addelement(0,'',$theData,'class="c-table-row-spacer"',$this->leftMargin);
			}

				// Header line is drawn
			$theData = Array();
			/*if ($this->disableSingleTableView)	{
				$theData[$titleCol] = '<span class="c-table">'.$GLOBALS['LANG']->sL($TCA[$table]['ctrl']['title'],1).'</span> ('.$this->totalItems.')';
			} else {
				$theData[$titleCol] = $this->linkWrapTable($table,'<span class="c-table">'.$GLOBALS['LANG']->sL($TCA[$table]['ctrl']['title'],1).'</span> ('.$this->totalItems.') <img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/'.($this->table?'minus':'plus').'bullet_list.gif','width="18" height="12"').' hspace="10" class="absmiddle" title="'.$GLOBALS['LANG']->getLL(!$this->table?'expandView':'contractView',1).'" alt="" />');
			}

				// CSH:
			$theData[$titleCol].= t3lib_BEfunc::cshItem($table,'',$this->backPath,'',FALSE,'margin-bottom:0px; white-space: normal;');

			if ($LOISmode)	{
				$out.='
					<tr>
						<td class="c-headLineTable" style="width:95%;">'.$theData[$titleCol].'</td>
					</tr>';

				if ($GLOBALS['BE_USER']->uc["edit_showFieldHelp"])	{
					$GLOBALS['LANG']->loadSingleTableDescription($table);
					if (isset($GLOBALS['TCA_DESCR'][$table]['columns']['']))	{
						$onClick = 'vHWin=window.open(\'view_help.php?tfID='.$table.'.\',\'viewFieldHelp\',\'height=400,width=600,status=0,menubar=0,scrollbars=1\');vHWin.focus();return false;';
						$out.='
					<tr>
						<td class="c-tableDescription">'.t3lib_BEfunc::helpTextIcon($table,'',$this->backPath,TRUE).$GLOBALS['TCA_DESCR'][$table]['columns']['']['description'].'</td>
					</tr>';
					}
				}
			} else {
				$theUpIcon = ($table=='pages'&&$this->id&&isset($this->pageRow['pid'])) ? '<a href="'.htmlspecialchars($this->listURL($this->pageRow['pid'])).'" onclick="setHighlight('.$this->pageRow['pid'].')"><img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/i/pages_up.gif','width="18" height="16"').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.upOneLevel',1).'" alt="" /></a>':'';
				$out.=$this->addelement(1,$theUpIcon,$theData,' class="c-headLineTable"','');
			}*/

			If (!$LOISmode)	{
					// Fixing a order table for sortby tables
				$this->currentTable = array();
				$currentIdList = array();
				$doSort = ($TCA[$table]['ctrl']['sortby'] && !$this->sortField);

				$prevUid = 0;
				$prevPrevUid = 0;
				$accRows = array();	// Accumulate rows here
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))	{
					$accRows[] = $row;
					$currentIdList[] = $row['uid'];
					if ($doSort)	{
						if ($prevUid)	{
							$this->currentTable['prev'][$row['uid']] = $prevPrevUid;
							$this->currentTable['next'][$prevUid] = '-'.$row['uid'];
							$this->currentTable['prevUid'][$row['uid']] = $prevUid;
						}
						$prevPrevUid = isset($this->currentTable['prev'][$row['uid']]) ? -$prevUid : $row['pid'];
						$prevUid=$row['uid'];
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result);

					// CSV initiated
				if ($this->csvOutput) $this->initCSV();

					// Render items:
				$this->CBnames=array();
				$this->duplicateStack=array();
				$this->eCounter=$this->firstElementNumber;

				$iOut = '';
				$cc = 0;
				foreach($accRows as $row)	{

						// Forward/Backwards navigation links:
					list($flag,$code) = $this->fwd_rwd_nav($table);
					$iOut.=$code;

						// If render item, increment counter and call function
					if ($flag)	{
						$cc++;
						$iOut.= $this->renderListRow($table,$row,$cc,$titleCol,$thumbsCol);

							// If localization view is enabled it means that the selected records are either default or All language and here we will not select translations which point to the main record:
						if ($this->localizationView && $l10nEnabled)	{

								// Look for translations of this record:
							$translations = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
								$selFieldList,
								$table,
								'pid='.$row['pid'].
									' AND '.$TCA[$table]['ctrl']['languageField'].'>0'.
									' AND '.$TCA[$table]['ctrl']['transOrigPointerField'].'='.intval($row['uid']).
									t3lib_BEfunc::deleteClause($table).
									t3lib_BEfunc::versioningPlaceholderClause($table)
							);

								// For each available translation, render the record:
							if (is_array($translations)) {
								foreach($translations as $lRow)	{
									if ($GLOBALS['BE_USER']->checkLanguageAccess($lRow[$TCA[$table]['ctrl']['languageField']]))	{
										$currentIdList[] = $lRow['uid'];
										$iOut.=$this->renderListRow($table,$lRow,$cc,$titleCol,$thumbsCol,18);
									}
								}
							}
						}
					}

						// Counter of total rows incremented:
					$this->eCounter++;
				}

					// The header row for the table is now created:
				$out.=$this->renderListHeader($table,$currentIdList);
			}

				// The list of records is added after the header:
			$out.=$iOut;

				// ... and it is all wrapped in a table:
			$out='



			<!--
				DB listing of elements:	"'.htmlspecialchars($table).'"
			-->
				<table border="0" cellpadding="0" cellspacing="0" class="typo3-dblist'.($LOISmode?' typo3-dblist-overview':'').'">
					'.$out.'
				</table>';

				// Output csv if...
			if ($this->csvOutput)	$this->outputCSV($table);	// This ends the page with exit.
		}

			// Return content:
		return $out;
	}
	
	function getButtons()
	{
		$buttons = parent::getButtons();
		if (isset($buttons['paste']))
			$buttons['paste'] = '<a href="' . htmlspecialchars($this->clipObj->pasteUrl('', $this->id, 1)) . '" onclick="' . htmlspecialchars('return ' . $this->clipObj->confirmMsg('pages', $this->pageRow, 'into', $elFromTable)) . '">' .
				'<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/clip_pasteafter.gif') . ' title="' . $LANG->getLL('clip_paste', 1) . '" alt="" />' .
				'</a>';
	}
}
