<?php
/****************************************************
CallerService.php

This file uses the constants.php to get parameters needed 
to make an API call and calls the server.if you want use your
own credentials, you have to change the constants.php

Called by TransactionDetails.php, ReviewOrder.php, 
DoDirectPaymentReceipt.php and DoExpressCheckoutPayment.php.

****************************************************/
global $MVX;

require_once($MVX->plugin_path.'lib/paypal/constants.php');

if(defined('API_USERNAME'))
$API_UserName=API_USERNAME;

if(defined('API_PASSWORD'))
$API_Password=API_PASSWORD;

if(defined('API_SIGNATURE'))
$API_Signature=API_SIGNATURE;

if(defined('API_ENDPOINT'))
$API_Endpoint =API_ENDPOINT;

$version=VERSION;

if(defined('SUBJECT'))
$subject = SUBJECT;
// below three are needed if used permissioning
if(defined('AUTH_TOKEN'))
$AUTH_token= AUTH_TOKEN;

if(defined('AUTH_SIGNATURE'))
$AUTH_signature=AUTH_SIGNATURE;

if(defined('AUTH_TIMESTAMP'))
$AUTH_timestamp=AUTH_TIMESTAMP;


function nvpHeader() {
	global $API_Endpoint,$version,$API_UserName,$API_Password,$API_Signature,$nvp_Header, $subject, $AUTH_token,$AUTH_signature,$AUTH_timestamp;
	$nvpHeaderStr = "";
	
	if(defined('AUTH_MODE')) {
	//$AuthMode = "3TOKEN"; //Merchant's API 3-TOKEN Credential is required to make API Call.
	//$AuthMode = "FIRSTPARTY"; //Only merchant Email is required to make EC Calls.
	//$AuthMode = "THIRDPARTY";Partner's API Credential and Merchant Email as Subject are required.
	$AuthMode = "AUTH_MODE"; 
	} 
	else {
	
	if( defined('API_USERNAME') && defined('API_PASSWORD') && defined('API_SIGNATURE') && defined('SUBJECT') ) {
		$AuthMode = "THIRDPARTY";
	}
	
	else if( defined('API_USERNAME') && defined('API_PASSWORD') && defined('API_SIGNATURE') ) {
		$AuthMode = "3TOKEN";
	}
	
	else if( defined('AUTH_TOKEN') && defined('AUTH_SIGNATURE') && defined('AUTH_TIMESTAMP') ) {
		$AuthMode = "PERMISSION";
	}
		elseif(defined('SUBJECT')) {
		$AuthMode = "FIRSTPARTY";
	}
	}
	
	switch($AuthMode) {
	
	case "3TOKEN" : 
			$nvpHeaderStr = "&PWD=".urlencode(API_PASSWORD)."&USER=".urlencode(API_USERNAME)."&SIGNATURE=".urlencode(API_SIGNATURE);
			break;
	case "FIRSTPARTY" :
			$nvpHeaderStr = "&SUBJECT=".urlencode(SUBJECT);
			break;
	case "THIRDPARTY" :
			$nvpHeaderStr = "&PWD=".urlencode(API_PASSWORD)."&USER=".urlencode(API_USERNAME)."&SIGNATURE=".urlencode(API_SIGNATURE)."&SUBJECT=".urlencode(SUBJECT);
			break;		
	case "PERMISSION" :
				$nvpHeaderStr = formAutorization(AUTH_TOKEN, AUTH_SIGNATURE, AUTH_TIMESTAMP);
				break;
	}
	return $nvpHeaderStr;
}

/**
  * hash_call: Function to perform the API call to PayPal using API signature
  * @methodName is name of API  method.
  * @nvpStr is nvp string.
  * returns an associtive array containing the response from the server.
*/


function hash_call($methodName, $nvpStr)
{
	global $MVX, $API_Endpoint,$version,$API_UserName,$API_Password,$API_Signature,$nvp_Header, $subject, $AUTH_token,$AUTH_signature,$AUTH_timestamp;
	// form header string
	$nvpheader=nvpHeader();
	
	//setting the curl parameters.
	$ch = curl_init();
	//doProductVendorLOG(API_ENDPOINT);
	curl_setopt($ch, CURLOPT_URL, API_ENDPOINT);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);

	//turning off the server and peer verification(TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POST, 1);
	
	//in case of permission APIs send headers as HTTPheders
	if( defined('AUTH_TOKEN') && defined('AUTH_SIGNATURE') && defined('AUTH_TIMESTAMP') ) {
		$headers_array[] = "X-PP-AUTHORIZATION: ".$nvpheader;
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_array);
    	curl_setopt($ch, CURLOPT_HEADER, false);
	}
	else {
		$nvpStr=$nvpheader.$nvpStr;
	}
    //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
   //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
	if(USE_PROXY)
	curl_setopt ($ch, CURLOPT_PROXY, PROXY_HOST.":".PROXY_PORT); 

	//check if version is included in $nvpStr else include the version.
	if(strlen(str_replace('VERSION=', '', strtoupper($nvpStr))) == strlen($nvpStr)) {
		$nvpStr = "&VERSION=" . urlencode(VERSION) . $nvpStr;	
	}
	
	$nvpreq="METHOD=".urlencode($methodName).$nvpStr;
	
	//setting the nvpreq as POST FIELD to curl
	curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);

	//getting response from server
	$response = curl_exec($ch);
	//convrting NVPResponse to an Associative Array
	$nvpResArray=deformatNVP($response);
	$nvpReqArray=deformatNVP($nvpreq);
	$_SESSION['nvpReqArray']=$nvpReqArray;

	if (curl_errno($ch)) {
		// moving to display page to display curl errors
		$_SESSION['curl_error_no']=curl_errno($ch) ;
		$_SESSION['curl_error_msg']=curl_error($ch);
	}
	//closing the curl
	curl_close($ch);
	return $nvpResArray;
}

/** This function will take NVPString and convert it to an Associative Array and it will decode the response.
  * It is usefull to search for a particular key and displaying arrays.
  * @nvpstr is NVPString.
  * @nvpArray is Associative Array.
  */

function deformatNVP($nvpstr)
{

	$intial=0;
 	$nvpArray = array();


	while(strlen($nvpstr)){
		//postion of Key
		$keypos= strpos($nvpstr,'=');
		//position of value
		$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);

		/*getting the Key and Value values and storing in a Associative Array*/
		$keyval=substr($nvpstr,$intial,$keypos);
		$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
		//decoding the respose
		$nvpArray[urldecode($keyval)] =urldecode( $valval);
		$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
     }
	return $nvpArray;
}
function formAutorization($auth_token,$auth_signature,$auth_timestamp)
{
	$authString="token=".$auth_token.",signature=".$auth_signature.",timestamp=".$auth_timestamp ;
	return $authString;
}
?>
