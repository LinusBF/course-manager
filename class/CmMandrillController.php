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

	public function getValidDomains(){
		try{
			$aDomains = $this->oMandrill->senders->domains();
		}catch (Mandrill_Error $e){
			return false;
		}
		$aValidDomains = array();

		foreach ($aDomains as $aDomain){
			if($aDomain['valid_signing'] && $aDomain['spf']['valid'] && $aDomain['dkim']['valid']){
				array_push($aValidDomains, $aDomain);
			}
		}

		return $aValidDomains;
	}

	public static function getNrOfTemplates(){
		$oCM = new CourseManager();
		$sApiKey = $oCM->getOptions()['mandrill']['api_key'];
		if ($sApiKey != -1){
			$mandrill = new Mandrill($sApiKey);
			$aTemplates = $mandrill->templates->getList();
			return count($aTemplates);
		} else
		{
			return 0;
		}
	}

	public function getTemplatesInfo(){
		$aTemplates = $this->oMandrill->templates->getList();
		$aStrippedTemplates = array();

		foreach ($aTemplates as $aTemplate){
			array_push($aStrippedTemplates, array(
					"slug" => $aTemplate["slug"],
					"name" => $aTemplate["name"],
					"subject" => $aTemplate["subject"],
					"labels" => $aTemplate["labels"],
				)
			);
		}

		return $aStrippedTemplates;
	}

	public function getTemplate($sSlug){
		$aTemplate = $this->oMandrill->templates->info($sSlug);

		return $aTemplate;
	}

	public static function setTemplateSlug($sSlug){
		$oCM = new CourseManager();
		$aMandrillOptions = $oCM->getOptions()['mandrill'];
		$aMandrillOptions['template_slug'] = $sSlug;
		$oCM->setOption('mandrill', $aMandrillOptions, true);
	}

	public function sendTokenToUser($iUserId, $iCourseId, $sToken){
		$oCM = new CourseManager();
		$aOptions = $oCM->getOptions()['mandrill'];
		$sUserEmail = CmUserManager::getUserById($iUserId)['email'];
		$sCourseName = CmCourse::getCourseByID($iCourseId)->getCourseName();
		$sFromEmail = $aOptions['from_email']."@".$aOptions['domain'];
		$template_content = array(array());
		$message = array(
			'subject' => $aOptions['subject_line'],
			'from_email' => $sFromEmail,
			'from_name' => $aOptions['from_name'],
			'to' => array(
				array(
					'email' => $sUserEmail,
					'type' => 'to'
				)
			),
			'headers' => array('Reply-To' => $sFromEmail),
			'merge_vars' => array(
				array(
					'rcpt' => $sUserEmail,
					'vars' => array(
						array(
							"name" => "cm_token",
							"content" => $sToken,
						),
						array(
							"name" => "cm_course_name",
							"content" => $sCourseName,
						)
					)
				)
			),
			'tags' => array('course_token'),
			'recipient_metadata' => array(
				array(
					'rcpt' => $sUserEmail,
					'values' => array('user_id' => $iUserId, "course_id" => $iCourseId, "token" => $sToken)
				)
			)
		);
		$async = false;
		$mResult = $this->oMandrill->messages->sendTemplate($aOptions['template_slug'], $template_content, $message, $async);

		return $mResult;
	}
}