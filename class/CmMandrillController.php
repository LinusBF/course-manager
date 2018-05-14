<?php

/**
 * Created by PhpStorm.
 * User: Linus
 * Date: 2018-05-07
 * Time: 22:29
 */

require_once CM_REALPATH."third-party/mandrill-php-5.2.0/Mandrill.php";

class CmMandrillController {

	private $oMandrill = null;

	/**
	 * CmMandrillController constructor.
	 */
	public function __construct() {
		$oCM = new CourseManager();
		$sApiKey = $oCM->getOptions()['mandrill']['api_key'];
		if ($sApiKey != -1){
			$this->oMandrill = new Mandrill($sApiKey);
		}
	}

	public function isMandrillActive(){
		return ($this->oMandrill !== null);
	}
}