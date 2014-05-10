<?php 

class user_labelexample {
	function getUserLabel(&$params, &$flexData) {
		$params['title'] = "UID: ".$params['row']['uid']."; ".$flexData['settings']['flexform']['description'];
	}
}

?>