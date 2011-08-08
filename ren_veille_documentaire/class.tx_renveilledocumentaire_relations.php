<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 In Cite Solution <technique@in-cite.net>
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
 * @desc 
 * Synchronise les relations entre les notices des veilles de sorte que ces relations soient bidirectionnelles
 *
 * @author Mickaël PAILLARD <mickael@in-cite.net>
 */
class tx_renveilledocumentaire_relations
{
	/**
	 * @desc pre-traitement des enregistrements traités par TCEmain avant la modification de la base.
	 *       gestion des relations a la modification d'un enregistrement de notices
	 * @author Mickaël Paillard <mickael@in-cite.net>
	 * @param $pi_aIncomingFieldArray array Données pour l'enregistrement.
	 * @param $pi_sTable string Nom de la table affectée.
	 * @param $pi_sId string Identifiant de l'enregistrement concerné.
	 * @param $pi_oTcemain object L'instance de TCEmain ayant effectué la mise à jour de la base.
	 */
	function processDatamap_preProcessFieldArray($pi_aIncomingFieldArray, $pi_sTable, $pi_sId, &$pi_oTcemain){
		global $TYPO3_DB, $BE_USER, $TYPO3_CONF_VARS;
		if ($pi_sTable == 'tx_renveilledocumentaire_notices' && !empty($pi_aIncomingFieldArray)) {
			$dump.=t3lib_div::view_array($pi_aIncomingFieldArray);
			$aExtConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['ren_veilledocumentaire']);
			if(isset($pi_aIncomingFieldArray['voir_aussi'])){
				$aNotices = $TYPO3_DB->exec_SELECTgetRows(
					'voir_aussi',
					$pi_sTable,
					'uid = '. $pi_sId 
				);
				if(is_array($aNotices)){
					$pi_aIncomingFieldArray['voir_aussi']=str_replace('tx_renveilledocumentaire_notices_','',$pi_aIncomingFieldArray['voir_aussi']);
					if($pi_aIncomingFieldArray['voir_aussi']!=$aNotices[0]['voir_aussi']){
						
						$aIncomingRelations=explode(',',trim($pi_aIncomingFieldArray['voir_aussi']));
						foreach ($aIncomingRelations as $key => $value)
							if (is_null($value) || $value=="")
								unset($aIncomingRelations[$key]); 
					
						$aOldRelations=array_values(explode(',',$aNotices[0]['voir_aussi']));
						foreach ($aOldRelations as $key => $value)
							if (is_null($value) || $value=="")
								unset($aOldRelations[$key]);
									
						$aToAdd=array_diff($aIncomingRelations,$aOldRelations);
						
						$aToSuppr=array_diff($aOldRelations,$aIncomingRelations);
						
						
						foreach($aToAdd as $distantNotice){
							$aDistantNotices = $TYPO3_DB->exec_SELECTgetRows('voir_aussi',$pi_sTable,'uid = '. $distantNotice);
							if(is_array($aDistantNotices)){
								$aDistantNotices=($aDistantNotices[0]['voir_aussi']!='')?explode(',',$aDistantNotices[0]['voir_aussi']):array();
								$aDistantNotices[]=$pi_sId;
								$aDistantValues=array('voir_aussi'=>implode(',',array_unique($aDistantNotices)));
								$GLOBALS['TYPO3_DB']->exec_UPDATEquery($pi_sTable,'uid='.$distantNotice ,$aDistantValues);	
								$dump.='dbadd'.$GLOBALS['TYPO3_DB']->UPDATEquery($pi_sTable,'uid='.$distantNotice ,$aDistantValues);	
							}
						}
						
						foreach($aToSuppr as $distantNotice){
							$aDistantNotices = $TYPO3_DB->exec_SELECTgetRows('voir_aussi',$pi_sTable,'uid = '. $distantNotice);
							if(is_array($aDistantNotices)){
								$aDistantNotices=explode(',',$aDistantNotices[0]['voir_aussi']);
								$dump.=t3lib_div::view_array(array_diff($aDistantNotices,$aToSuppr));
								$aDistantValues=array('voir_aussi'=>implode(',',array_diff($aDistantNotices,array(0=>$pi_sId))));
								$GLOBALS['TYPO3_DB']->exec_UPDATEquery($pi_sTable,'uid='.$distantNotice ,$aDistantValues);	
								$dump.='dbsuppr'.$GLOBALS['TYPO3_DB']->UPDATEquery($pi_sTable,'uid='.$distantNotice ,$aDistantValues);
							}
						}	
					}
				}
			}
			if(isset($pi_aIncomingFieldArray['actus'])){
				$aNotices = $TYPO3_DB->exec_SELECTgetRows(
					'actus',
					$pi_sTable,
					'uid = '. $pi_sId 
				);
				if(is_array($aNotices)){
					$pi_aIncomingFieldArray['actus']=str_replace('tt_news_','',$pi_aIncomingFieldArray['actus']);
					if($pi_aIncomingFieldArray['actus']!=$aNotices[0]['actus']){
						
						$aIncomingRelations=explode(',',trim($pi_aIncomingFieldArray['actus']));
						foreach ($aIncomingRelations as $key => $value)
							if (is_null($value) || $value=="")
								unset($aIncomingRelations[$key]); 
					
						$aOldRelations=array_values(explode(',',$aNotices[0]['actus']));
						foreach ($aOldRelations as $key => $value)
							if (is_null($value) || $value=="")
								unset($aOldRelations[$key]);
									
						$aToAdd=array_diff($aIncomingRelations,$aOldRelations);
						
						$aToSuppr=array_diff($aOldRelations,$aIncomingRelations);
						
						
						foreach($aToAdd as $distantNew){
							$aDistantNews = $TYPO3_DB->exec_SELECTgetRows('ren_veilledocumentaire_notices','tt_news','uid = '. $distantNew);
							if(is_array($aDistantNews)){
								$aDistantNews=($aDistantNews[0]['ren_veilledocumentaire_notices']!='')?explode(',',$aDistantNews[0]['ren_veilledocumentaire_notices']):array();
								$aDistantNews[]=$pi_sId;
								$aDistantValues=array('ren_veilledocumentaire_notices'=>implode(',',array_unique($aDistantNews)));
								$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_news','uid='.$distantNew ,$aDistantValues);	
								$dump.='dbadd'.$GLOBALS['TYPO3_DB']->UPDATEquery('tt_news','uid='.$distantNew ,$aDistantValues);	
							}
						}
						
						foreach($aToSuppr as $distantNew){
							$aDistantNews = $TYPO3_DB->exec_SELECTgetRows('ren_veilledocumentaire_notices','tt_news','uid = '. $distantNew);
							if(is_array($aDistantNews)){
								$aDistantNews=explode(',',$aDistantNews[0]['ren_veilledocumentaire_notices']);
								$dump.=t3lib_div::view_array(array_diff($aDistantNews,$aToSuppr));
								$aDistantValues=array('ren_veilledocumentaire_notices'=>implode(',',array_diff($aDistantNews,array(0=>$pi_sId))));
								$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_news','uid='.$distantNew ,$aDistantValues);
								$dump.='dbsuppr'.$GLOBALS['TYPO3_DB']->UPDATEquery('tt_news','uid='.$distantNew ,$aDistantValues);
							}
						}	
					}
				}
			}
		}
		
		
		if ($pi_sTable == 'tt_news' && !empty($pi_aIncomingFieldArray)) {
			$aExtConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['ren_veilledocumentaire']);
			if(isset($pi_aIncomingFieldArray['ren_veilledocumentaire_notices'])){
				$aNews = $TYPO3_DB->exec_SELECTgetRows(
					'ren_veilledocumentaire_notices',
					$pi_sTable,
					'uid = '. $pi_sId 
				);
				if(is_array($aNews)){
					$pi_aIncomingFieldArray['ren_veilledocumentaire_notices']=str_replace('tx_renveilledocumentaire_notices_','',$pi_aIncomingFieldArray['ren_veilledocumentaire_notices']);
					if($pi_aIncomingFieldArray['ren_veilledocumentaire_notices']!=$aNews[0]['ren_veilledocumentaire_notices']){
						
						$aIncomingRelations=explode(',',trim($pi_aIncomingFieldArray['ren_veilledocumentaire_notices']));
						foreach ($aIncomingRelations as $key => $value)
							if (is_null($value) || $value=="")
								unset($aIncomingRelations[$key]); 
					
						$aOldRelations=array_values(explode(',',$aNotices[0]['ren_veilledocumentaire_notices']));
						foreach ($aOldRelations as $key => $value)
							if (is_null($value) || $value=="")
								unset($aOldRelations[$key]);
									
						$aToAdd=array_diff($aIncomingRelations,$aOldRelations);
						
						$aToSuppr=array_diff($aOldRelations,$aIncomingRelations);
						
						
						foreach($aToAdd as $distantNotice){
							$aDistantNotices = $TYPO3_DB->exec_SELECTgetRows('actus','tx_renveilledocumentaire_notices','uid = '. $distantNotice);
							if(is_array($aDistantNotices)){
								$aDistantNotices=($aDistantNotices[0]['actus']!='')?explode(',',$aDistantNotices[0]['actus']):array();
								$aDistantNotices[]=$pi_sId;
								$aDistantValues=array('actus'=>implode(',',array_unique($aDistantNotices)));
								$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_renveilledocumentaire_notices','uid='.$distantNotice ,$aDistantValues);	
								$dump.='dbadd'.$GLOBALS['TYPO3_DB']->UPDATEquery('tx_renveilledocumentaire_notices','uid='.$distantNotice ,$aDistantValues);	
							}
						}
						
						foreach($aToSuppr as $distantNotice){
							$aDistantNotices = $TYPO3_DB->exec_SELECTgetRows('actus','tx_renveilledocumentaire_notices','uid = '. $distantNotice);
							if(is_array($aDistantNotices)){
								$aDistantNotices=explode(',',$aDistantNotices[0]['actus']);
								$dump.=t3lib_div::view_array(array_diff($aDistantNotices,$aToSuppr));
								$aDistantValues=array('actus'=>implode(',',array_diff($aDistantNotices,array(0=>$pi_sId))));
								$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_renveilledocumentaire_notices','uid='.$distantNotice ,$aDistantValues);	
								$dump.='dbsuppr'.$GLOBALS['TYPO3_DB']->UPDATEquery($pi_sTable,'uid='.$distantNotice ,$aDistantValues);
							}
						}	
					}
				}
			}
		}
		/*if($BE_USER->user['username']=='mickael'){
				/*$dump='';
				$count=0;
				$allNews = $TYPO3_DB->exec_SELECTgetRows(
					'uid,ren_veilledocumentaire_notices',
					'tt_news',
					'deleted=0 AND hidden=0'
					);
				if(is_array($allNews)){
					foreach($allNews as $myNew){
						if ($myNew['ren_veilledocumentaire_notices']!=''){
							$aIncomingRelations=explode(',',trim($myNew['ren_veilledocumentaire_notices']));
							foreach($aIncomingRelations as $distantNotice){
								$aDistantNotices = $TYPO3_DB->exec_SELECTgetRows('actus','tx_renveilledocumentaire_notices','uid = '. $distantNotice);
								if(is_array($aDistantNotices)){
									$aDistantNotices=($aDistantNotices[0]['actus']!='')?explode(',',$aDistantNotices[0]['actus']):array();
									$aDistantNotices[]=$myNew['uid'];
									$aDistantValues=array('actus'=>implode(',',array_unique($aDistantNotices)));
									$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_renveilledocumentaire_notices','uid='.$distantNotice ,$aDistantValues);
									$dump.='<br />'.$GLOBALS['TYPO3_DB']->UPDATEquery('tx_renveilledocumentaire_notices','uid='.$distantNotice ,$aDistantValues);
									$count++;									
								}
							}
						}
					}
				}
				
				echo '<div style="position:fixed;z-index: 999; bottom: 1em;right: 5em; background-color: #FFF">'.$dump.'<br/> <br />'.$count.'</div>';
			}*/
	}
	
	/**
	 * @desc Post-traitement des enregistrements traités par TCEmain après la modification de la base.
	 *       ajout des relations a la creation d'un nouvel enregistrement de notices
	 * @author Virginie Sugère <vsugere@in-cite.net>
	 * @param $pi_sStatus string Indication de la création ou de la mise à jour de l'enregistrement.
	 * @param $pi_sTable string Nom de la table affectée.
	 * @param $pi_sId string Identifiant de l'enregistrement concerné.
	 * @param $pi_aFieldArray array Données de l'enregistrement.
	 * @param $pi_oTce object L'instance de TCEmain ayant effectué la mise à jour de la base.
	 */
	function processDatamap_afterDatabaseOperations($pi_sStatus, $pi_sTable, $pi_sId, $pi_aFieldArray, $pi_oTce) {
		global $TYPO3_DB, $BE_USER, $TYPO3_CONF_VARS;
		
		if ($pi_sTable == 'tx_renveilledocumentaire_notices' && !empty($pi_aFieldArray)) {
			$aExtConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['ren_veilledocumentaire']);
			if((isset($pi_aFieldArray['voir_aussi']))&&($pi_sStatus=='new')){
				$aIncomingRelations=explode(',',trim($pi_aFieldArray['voir_aussi']));
				foreach ($aIncomingRelations as $key => $value)
					if (is_null($value) || $value=="")
						unset($aIncomingRelations[$key]); 
				foreach($aIncomingRelations as $distantNotice){
					$aDistantNotices = $TYPO3_DB->exec_SELECTgetRows('voir_aussi',$pi_sTable,'uid = '. $distantNotice);
					if(is_array($aDistantNotices)){
						$aDistantNotices=($aDistantNotices[0]['voir_aussi']!='')?explode(',',$aDistantNotices[0]['voir_aussi']):array();
						$aDistantNotices[]=$pi_oTce->substNEWwithIDs[$pi_sId];
						$aDistantValues=array('voir_aussi'=>implode(',',array_unique($aDistantNotices)));
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery($pi_sTable,'uid='.$distantNotice ,$aDistantValues);	
						$dump.='<br/>'.$GLOBALS['TYPO3_DB']->UPDATEquery($pi_sTable,'uid='.$distantNotice ,$aDistantValues);	
					}
				}
			}
		}
		
		if ($pi_sTable == 'tt_news' && !empty($pi_aFieldArray)) {
			$aExtConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['ren_veilledocumentaire']);
			if((isset($pi_aFieldArray['ren_veilledocumentaire_notices']))&&($pi_sStatus=='new')){
				$aIncomingRelations=explode(',',trim($pi_aFieldArray['ren_veilledocumentaire_notices']));
				foreach ($aIncomingRelations as $key => $value)
					if (is_null($value) || $value=="")
						unset($aIncomingRelations[$key]); 
				foreach($aIncomingRelations as $distantNotice){
					$aDistantNotices = $TYPO3_DB->exec_SELECTgetRows('actus','tx_renveilledocumentaire_notices','uid = '. $distantNotice);
					if(is_array($aDistantNotices)){
						$aDistantNotices=($aDistantNotices[0]['actus']!='')?explode(',',$aDistantNotices[0]['actus']):array();
						$aDistantNotices[]=$pi_oTce->substNEWwithIDs[$pi_sId];
						$aDistantValues=array('actus'=>implode(',',array_unique($aDistantNotices)));
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_renveilledocumentaire_notices','uid='.$distantNotice ,$aDistantValues);	
						$dump.='<br/>'.$GLOBALS['TYPO3_DB']->UPDATEquery('tx_renveilledocumentaire_notices','uid='.$distantNotice ,$aDistantValues);	
					}
				}
			}
		}
		
	}
}