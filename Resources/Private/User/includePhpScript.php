<?php

class contentdesigner_includescript {
  public function main($content, $conf) {
	$f = $GLOBALS['TSFE']->cObj->cObjGetSingle($conf['includeScript'], $conf['includeScript.']);
	
	if ( file_exists($f) ) {
		ob_start();
		include($f);
		return ob_get_clean();
	}
  }
}

?>