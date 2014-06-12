<?php
namespace KERN23\ContentDesigner\Hooks;


/* Hook: if in install tool explicitADmode set to explicitAllow these Elements couldn't be used
 *       This is a workaround to make it possible
 */
class BackendUserAuthentication extends \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication {
	
	public function fetchGroups_postProcessing($grList, $pObj) {
		$cdItems = \KERN23\ContentDesigner\Utility\TypoScript::loadConfig($config, 'tx_contentdesigner');
		
		if( count($cdItems) > 0 ) {
			$dataList = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $pObj->dataLists['explicit_allowdeny'], TRUE);
			
			foreach($cdItems as $cdObjKey => $CTypeData) {
				//$pObj->dataLists['explicit_allowdeny'] .= ',tt_content:CType:'.substr($cdObjKey,0,strlen($cdObjKey)-1).':ALLOW';
				$dataList[] = 'tt_content:CType:'.substr($cdObjKey,0,strlen($cdObjKey)-1).':ALLOW';
			}
			
			$pObj->dataLists['explicit_allowdeny'] = ','.implode(',', $dataList);
		}
	}
}

?>