<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 In Cite Solution <technique@in-cite.net>
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

/**
 * @author Pierrick Caillon <pierrick@in-cite.net>
 * @desc Extension de la suggestion pour l'adapter aux mots clef.
 * @package TYPO3
 * @subpackage ren_veilledocumentaire
 */
class tx_renveilledocumentaire_TCEforms_Suggest_KeywordsReceiver extends t3lib_TCEforms_Suggest_DefaultReceiver
{
	protected $aRecordCache = array();

	/**
	 * @author Pierrick Caillon <pierrick@in-cite.net>
	 * @desc Requête la base à la recherche des résultats de suggestion.
	 * @param array $p_aParams Les paramètres de la requête.
	 * @param int $pi_iRecursionCounter Le compteur d'appel récursif.
	 * @return string Le chemin de l'enregistrement généré.
	 **/
	public function queryTable(&$p_aParams, $pi_iRecursionCounter = 0)
	{
		$rows = parent::queryTable($p_aParams, $pi_iRecursionCounter);
		uasort($rows, array('tx_renveilledocumentaire_TCEforms_Suggest_KeywordsReceiver', 'entryCompare'));
		$n = 0;
		foreach ($rows as $key => $row)
			$rows[$key]['text'] = $rows[$key]['text'] . '<!--' . ($n++) . '-->';
		return $rows;
	}
	
	public static function entryCompare($e1, $e2)
	{
		$res = strcmp($e1['path'], $e2['path']);
		if ($res == 0)
		{
			$res = strcmp($e1['label'], $e2['label']);
		}
		return $res;
	}
	
	/**
	 * @author Pierrick Caillon <pierrick@in-cite.net>
	 * @desc Génère le chemin de l'enregistrement dans l'arbre des mots clef.
	 * @param array $pi_aRow L'enregistrement pour lequel générer le chemin.
	 * @param int $pi_iUid L'uid de l'enregistrement.
	 * @return string Le chemin de l'enregistrement généré.
	 **/
	protected function getRecordPath($pi_aRow, $pi_iUid)
	{
		$iTitleLimit = max($this->config['maxPathTitleLength'], 0);

		$aRootLine = array();
		$aRecords = $this->getMMdata($pi_aRow);
		while (!empty($aRecords))
		{
			$aKeys = array_keys($aRecords);
			$sKey = $aKeys[0];
			$sTitle = $aRecords[$sKey][$GLOBALS['TCA'][$this->table]['ctrl']['label']];
			if ($iTitleLimit)
			{
				$sTitle = t3lib_div::fixed_lgd_cs($sTitle, $iTitleLimit);
			}
			array_unshift($aRootLine, $sTitle);
			$aRecords = $this->getMMdata($aRecords[$sKey]);
		}
		
		$sPath = implode(' / ', $aRootLine);
		
		return $sPath;
	}
	
	/**
	 * @author Pierrick Caillon <pierrick@in-cite.net>
	 * @desc Récupère les enregistrements parents par la relation MM inscrite dans le champ generic de la table des mots clef.
	 * @param array $pi_aRow L'enregistrement pour lequel récupérer les informations.
	 * @return array Les enregistrements parents.
	 **/
	private function getMMdata($pi_aRow)
	{
		if (!isset($this->aRecordCache[$pi_aRow['uid']]))
		{
			$aFieldConfig = $GLOBALS['TCA']['tx_renveilledocumentaire_keywords']['columns']['generic'];
			$oLoadDB = t3lib_div::makeInstance('t3lib_loadDBGroup');
			$oLoadDB->start($pi_aRow['generic'], $aFieldConfig['config']['allowed'], $aFieldConfig['config']['MM'], $pi_aRow['uid'], $this->table, $aFieldConfig['config']);
			$oLoadDB->getFromDB();
			$this->aRecordCache[$pi_aRow['uid']] = $oLoadDB->results['tx_renveilledocumentaire_keywords'];
		}
		return $this->aRecordCache[$pi_aRow['uid']];
	}
}
