<?php
/**
 * Created by PhpStorm.
 * User: Linus
 * Date: 2018-02-17
 * Time: 11:54
 */

class CmMailController{

	/*
	 * ----------  PRIVATE HELPER FUNCTIONS  ----------
	 */


	/**
	 * Checks if an API key has been provided by an admin
	 *
	 * @return bool
	 */
	private static function _isApiKeySet(){
		$oCM = new CourseManager();
		$sMcApiKey = $oCM->getOptions()['mail_chimp']['api_key'];
		return ($sMcApiKey !== -1);
	}


	/**
	 * Checks if a List ID has been set to store customer emails in (REQUIRED for MailChimp integration with Course Manager)
	 *
	 * @return bool
	 */
	private static function _isListSet(){
		$oCM = new CourseManager();
		$iListId = $oCM->getOptions()['mail_chimp']['list_id'];

		return ($iListId !== -1);
	}


	/**
	 * Checks if a Group ID has been set to store customer emails in (OPTIONAL for MailChimp integration with Course Manager)
	 *
	 * @return bool
	 */
	private static function _isGroupSet(){
		$oCM = new CourseManager();
		$iGroupId = $oCM->getOptions()['mail_chimp']['group_id'];

		return ($iGroupId !== -1);
	}


	/**
	 * Checks if a Template ID has been set to store customer emails in (OPTIONAL for MailChimp integration with Course Manager)
	 *
	 * @return bool
	 */
	private static function _isTemplateSet(){
		$oCM = new CourseManager();
		$iTemplateId = $oCM->getOptions()['mail_chimp']['template_id'];

		return ($iTemplateId !== -1);
	}


	/**
	 * @return mixed
	 */
	private static function _getApiParams(){
		$oCM = new CourseManager();
		$aMcOptions = $oCM->getOptions()['mail_chimp'];
		if($aMcOptions['api_key'] !== -1){
			$aMcOptions['api_server'] =  explode("-",$aMcOptions['api_key'])[1];
			return $aMcOptions;
		} else{
			return false;
		}
	}


	/**
	 * @param string $sURL
	 * @param string $sType - GET, POST, UPDATE, PUT or DELETE
	 * @param bool $aData - array("param" => "value")
	 *
	 * @return mixed
	 */
	private static function _makeApiCall($sURL, $sType, $aData = false){
		if(substr( $sURL, 0, 4 ) === "http"){
			$sURL = explode(".com/", $sURL)[1];
		}
		if(is_numeric(substr( $sURL, 0, 1))){
			$sURL = explode("/", $sURL, 2)[1];
		}
		if (substr( $sURL, 0, 1 ) === "/"){
			$sURL = substr( $sURL, 1);
		}

		$aApiParams = self::_getApiParams();
		$sURL = "https://".$aApiParams['api_server'].".api.mailchimp.com/3.0/".$sURL;

		$oCurl = curl_init();

		$aHeaders = array('Content-Type: application/json');

		switch ($sType)
		{
			case "POST":
				curl_setopt($oCurl, CURLOPT_POST, 1);
				curl_setopt($oCurl, CURLOPT_HTTPHEADER, $aHeaders);
				if ($aData)
					curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($aData));
				break;
			case "PUT":
				curl_setopt($oCurl, CURLOPT_PUT, 1);
				break;
			case "PATCH":
				curl_setopt($oCurl, CURLOPT_CUSTOMREQUEST, 'PATCH');
				curl_setopt($oCurl, CURLOPT_HTTPHEADER, $aHeaders);
				if ($aData)
					curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($aData));
				break;
			default:
				if ($aData)
					$sURL = sprintf("%s?%s", $sURL, http_build_query($aData));
		}

		//Specific cURL settings for mail chimp requests
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);

		curl_setopt($oCurl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($oCurl, CURLOPT_USERPWD, "course_manager:" . $aApiParams['api_key']);

		curl_setopt($oCurl, CURLOPT_URL, $sURL);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);

		$mResult = curl_exec($oCurl);

		curl_close($oCurl);

		return $mResult;
	}


	/**
	 * @param string $sCampaignId
	 *
	 * @return bool
	 */
	private static function _getCampaignByID($sCampaignId){
		$mResult = json_decode(self::_makeApiCall( "/campaigns/" . $sCampaignId, "GET"));
		if(!is_object($mResult)){
			return false;
		}
		return $mResult;
	}

	/*
	 * ----------  PUBLIC HELPER FUNCTIONS  ----------
	 */


	/**
	 * @return bool
	 */
	public static function checkApiKey() {
		return (is_object(json_decode(self::_makeApiCall("/list","GET"))));
	}


	/**
	 * @return bool
	 */
	public static function mailChimpActive(){
		return (self::_isApiKeySet() && self::_isListSet() && self::checkApiKey() && self::_isTemplateSet());
	}


	/**
	 * @return array
	 */
	public static function getArraysForSettings(){
		$aLists = self::_getListsCall();
		$aGroups = self::_getGroupsCall(self::getListId());

		return array("lists" => $aLists, "groups" => $aGroups);
	}


	/*
	 * ----------  API FUNCTIONS  ----------
	 */


	/**
	 * @param string $sApiKey
	 *
	 * @return bool
	 */
	private static function _setApiKeyCall($sApiKey){
		$oCM = new CourseManager();

		$chimp_settings            = $oCM->getOptions()['mail_chimp'];
		$chimp_settings['api_key'] = $sApiKey;
		$chimp_settings['list_id'] = -1;
		$chimp_settings['group_id'] = -1;

		$oCM->setOption( 'mail_chimp', $chimp_settings, true );

		return self::checkApiKey();
	}


	/**
	 * @param string $sApiKey
	 *
	 * @return bool
	 */
	public static function setApiKey($sApiKey){
		return self::_setApiKeyCall($sApiKey);
	}


	/*
	 * ----------  LIST FUNCTIONS  ----------
	 */


	/**
	 * @param int $iIndex
	 * @param int $iNrOfItems
	 *
	 * @return array|mixed|object
	 */
	private static function _getListsCall($iIndex = 0, $iNrOfItems = 99){
		$sUrl = "/lists";
		$jResult = self::_makeApiCall($sUrl, "GET", array("offset" => $iIndex, "count" => $iNrOfItems));
		return json_decode($jResult);
	}


	/**
	 * @param int $iIndex
	 * @param int $iNrOfItems
	 * @param bool $blBaseObject
	 *
	 * @return array|bool|mixed|object
	 */
	public static function getLists($iIndex = 0, $iNrOfItems = 99, $blBaseObject = false){
		if(!self::_isApiKeySet())
			return false;

		$oLists = self::_getListsCall($iIndex, $iNrOfItems);
		if($blBaseObject){
			unset($oLists->_links);
			return $oLists;
		}

		return $oLists->lists;
	}


	/**
	 * @return int
	 */
	public static function getListId() {
		return self::_getApiParams()['list_id'];
	}


	/**
	 * @param int $iListID
	 *
	 * @return null
	 */
	private static function _setListCall($iListID){
		$oCM = new CourseManager();

		$chimp_settings = $oCM->getOptions()['mail_chimp'];
		$iOldId = $chimp_settings['list_id'];
		$chimp_settings['list_id'] = $iListID;
		$chimp_settings['group_id'] = -1;
		$iCampaignId = $chimp_settings['campaign_id'];

		$oCM->setOption( 'mail_chimp', $chimp_settings, true );

		// TODO - Create Campaign for token delivery with API Calls
		if($iOldId !== -1 && $iCampaignId !== -1){
			// Modify existing campaign
			$oCampaign = self::_getCampaignByID($iCampaignId);
			$aCampaignData = array(
				"recipients" => array(
					"list_id" => $iListID
				)
			);

			$mResult = self::_makeApiCall("/campaigns/".$oCampaign->id, "PATCH", $aCampaignData);
		} else{
			// Create new campaign
			$aCampaignData = array(
				"type" => "regular",
				"recipients" => array(
					"list_id" => $iListID
				),
				"settings" => array(
					"subject_line" => "Your Course Token",
					"title" => "Receipt (Course Manager)",
					"reply_to" => "purchasetest@linusbf.com",
					"from_name" => "Course Manager"
				),
			);

			$mResult = self::_makeApiCall("/campaigns", "POST", $aCampaignData);
		}

		return json_decode($mResult);
	}


	/**
	 * @param int $iListID
	 *
	 * @return bool|null
	 */
	public static function setList($iListID){
		if(self::_isApiKeySet()){
			return self::_setListCall($iListID);
		} else{
			return false;
		}
	}


	/*
	 * ----------  GROUP FUNCTIONS  ----------
	 */

	/**
	 * @param int $iListID
	 * @param int $iIndex
	 * @param int $iNrOfItems
	 *
	 * @return array|mixed|object
	 */
	private static function _getGroupsCall($iListID, $iIndex = 0, $iNrOfItems = 10){
		$sUrl = "/lists/".$iListID."/interest-categories";
		$jResult = self::_makeApiCall($sUrl, "GET", array("offset" => $iIndex, "count" => $iNrOfItems));
		return json_decode($jResult);
	}


	/**
	 * @param int $iIndex
	 * @param int $iNrOfItems
	 * @param bool $blBaseObject
	 *
	 * @return array|bool|mixed|object
	 */
	public static function getGroups($iIndex = 0, $iNrOfItems = 10, $blBaseObject = false){
		if(!self::_isApiKeySet() || !self::_isListSet())
			return false;

		$oGroups = self::_getGroupsCall(self::getListId(), $iIndex, $iNrOfItems);
		if($blBaseObject){
			unset($oGroups->_links);
			return $oGroups;
		}

		return $oGroups->categories;
	}


	/**
	 * @return int
	 */
	public static function getGroupId() {
		return self::_getApiParams()['group_id'];
	}


	/**
	 * @param int $iGroupID
	 *
	 * @return null
	 */
	private static function _setGroupCall($iGroupID){
		$oCM = new CourseManager();

		$chimp_settings            = $oCM->getOptions()['mail_chimp'];
		$chimp_settings['group_id'] = $iGroupID;

		$oCM->setOption( 'mail_chimp', $chimp_settings, true );

		// TODO - Create Campaign for offers with API Calls

		return null;
	}

	/**
	 * @param int $iGroupID
	 *
	 * @return bool|null
	 */
	public static function setGroup($iGroupID){
		if(self::_isApiKeySet() && self::_isListSet()){
			return self::_setGroupCall($iGroupID);
		} else{
			return false;
		}
	}


	/*
	 * ----------  TEMPLATE FUNCTIONS  ----------
	 */

	/**
	 * @param int $iIndex
	 * @param int $iNrOfItems
	 *
	 * @return array|mixed|object
	 */
	private static function _getTemplatesCall($iIndex = 0, $iNrOfItems = 10){
		$sUrl = "/templates";
		$jResult = self::_makeApiCall($sUrl, "GET", array("offset" => $iIndex, "count" => $iNrOfItems));
		return json_decode($jResult);
	}


	/**
	 * @param int $iIndex
	 * @param int $iNrOfItems
	 * @param bool $blBaseObject
	 *
	 * @return array|bool|mixed|object
	 */
	public static function getTemplates($iIndex = 0, $iNrOfItems = 10, $blBaseObject = false){
		if(!self::_isApiKeySet() || !self::_isListSet())
			return false;

		$oTemplates = self::_getTemplatesCall($iIndex, $iNrOfItems);
		if($blBaseObject){
			unset($oTemplates->_links);
			return $oTemplates;
		}

		return $oTemplates->templates;
	}


	/**
	 * @return int
	 */
	public static function getTemplateId() {
		return self::_getApiParams()['template_id'];
	}


	/**
	 * @param int $iTemplateID
	 *
	 * @return null
	 */
	private static function _setTemplateCall($iTemplateID){
		$oCM = new CourseManager();

		$chimp_settings            = $oCM->getOptions()['mail_chimp'];
		$chimp_settings['template_id'] = $iTemplateID;

		$oCM->setOption( 'mail_chimp', $chimp_settings, true );

		$iCampaign_id = $chimp_settings['campaign_id'];
		if($iCampaign_id !== -1){
			$oCampaign = self::_getCampaignByID($iCampaign_id);
			if($oCampaign === false){
				return -1;
			}
			$aSettings = array("settings" => array(
				"template_id" => $iTemplateID,
			));

			$mResult = self::_makeApiCall("/campaigns/".$oCampaign->id, "PATCH", $aSettings);

			return json_decode($mResult);
		}

		return -1;
	}

	/**
	 * @param int $iTemplateID
	 *
	 * @return bool|null
	 */
	public static function setTemplate($iTemplateID){
		if(self::_isApiKeySet() && self::_isListSet()){
			return self::_setTemplateCall($iTemplateID);
		} else{
			return false;
		}
	}


	public static function getTokenMailTemplate($iEventID, $sCourseToken){
		$oCM = new CourseManager();

		$chimp_settings = $oCM->getOptions()['mail_chimp'];
		$iCampaignId = $chimp_settings['campaign_id'];
		if($iCampaignId !== -1){
			$oCampaign = self::_getCampaignByID($iCampaignId);
			if(!is_object($oCampaign)){
				return false;
			}
			$mResult = json_decode(self::_makeApiCall( "/campaigns/" . $iCampaignId . "/content", "GET"));
			if(!is_object($mResult)){
				return false;
			}
			$sHTML = $mResult->archive_html;
			// Add token
			$sHTML = str_replace("{course_token}", $sCourseToken, $sHTML);
			$sHTML = str_replace("[UNIQID]", strval($iEventID), $sHTML);

			return $sHTML;
		}
		return false;
	}

}

