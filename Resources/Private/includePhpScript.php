<?php

class contentdesigner_includescript {
  public function main($content, $conf) {
    ob_start();
    include_once($GLOBALS['TSFE']->cObj->cObjGetSingle($conf['includeScript'], $conf['includeScript.']));
    return ob_get_clean();
  }
}

?>