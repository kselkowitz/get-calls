<?php

define("SERVER", "localhost");
define("SUPERUSER", "superuser");
define("PASSWORD", "password");
define("CLIENTID", "clientid");
define("CLIENTSECRET", "clientsecret");
define("DOMAIN","domain");



/* First Step is to get a new Access token to given server.*/
$query = array(
		'grant_type'	=> 'password',
		'username'		=> SUPERUSER,
		'password'		=> PASSWORD,
		'client_id'		=> CLIENTID,
		'client_secret'		=> CLIENTSECRET,
);

$postFields = http_build_query($query);
$http_response = "";

$curl_result = __doCurl("https://".SERVER."/ns-api/oauth2/token", CURLOPT_POST, NULL, NULL, $postFields, $http_response);

if (!$curl_result){
    echo "error doing curl getting key";
    exit;
}

$token = json_decode($curl_result, /*assoc*/true);

if (!isset($token['access_token'])) {
    echo "failure getting access token";
    exit;
}

$token = $token['access_token'];

/* Get call count */
$query = array(
        'object'    => 'call',
        'action'        => "count",
        'domain'    =>    DOMAIN,
);
$callcount = __doCurl("https://".SERVER."/ns-api/", CURLOPT_POST, "Authorization: Bearer " . $token, $query, null, $http_response);

// print  call count
printf($callcount);

function __doCurl($url, $method, $authorization, $query, $postFields, &$http_response)
{
	$start= microtime(true);
	$curl_options = array(
			CURLOPT_URL => $url . ($query ? '?' . http_build_query($query) : ''),
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FORBID_REUSE => true,
			CURLOPT_TIMEOUT => 60
	);

	$headers = array();
	if ($authorization != NULL)
	{
		if ("bus:bus" == $authorization)
			$curl_options[CURLOPT_USERPWD]=$authorization;
		else
			$headers[$authorization]=$authorization;
	}


	$curl_options[$method] = true;
	if ($postFields != NULL )
	{
		$curl_options[CURLOPT_POSTFIELDS] = $postFields;
	}

	if (sizeof($headers)>0)
		$curl_options[CURLOPT_HTTPHEADER] = $headers;

	$curl_handle = curl_init();
	curl_setopt_array($curl_handle, $curl_options);
	$curl_result = curl_exec($curl_handle);
	$http_response = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
	//print_r($http_response);
	curl_close($curl_handle);
	$end = microtime(true);
	if (!$curl_result)
		return NULL;
	else if ($http_response >= 400)
		return NULL;
	else
		return $curl_result;
}



?>
