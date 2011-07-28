<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_renveilledocumentaire_pi1.php', '_pi1', 'list_type', 0);


t3lib_extMgm::addPItoST43($_EXTKEY, 'pi2/class.tx_renveilledocumentaire_pi2.php', '_pi2', 'list_type', 0);
t3lib_extMgm::addPageTSConfig(
	'TCEFORM.suggest.tx_renveilledocumentaire_keywords.receiverClass = tx_renveilledocumentaire_TCEforms_Suggest_KeywordsReceiver' . chr(10) .
	'#TCEFORM.suggest.tx_renveilledocumentaire_keywords.searchWholePhrase = 1' . chr(10) .
	'TCEFORM.suggest.tx_renveilledocumentaire_keywords.maxItemsInResultList = 15'
);
?>