
<?php
# Replace with your application ID and application secret.
$applicationId = 'YOUR APPLICATION ID';
$applicationSecret = 'YOUR APPLICATION SECRET';
$title = "OAuth callback page";
$squareDomain = 'https://connect.squareup.com';
$authorizationCode = " ";
$accessToken = " ";
$description = "OAuth callback page to get authorization token and use it to get access tokens";

# Call the function
  try {
    if(isset($_GET["func"]) && $_GET["func"] === "renewOAuthToken") {
      $accessToken = renewOAuthToken(
        $_GET["accessToken"]);
    } elseif(isset($_GET["func"]) && $_GET["func"] === "revokeToken") {
      $accessToken = revokeToken(
        $_GET["accessToken"]);
    } 
    else {    
      $authorizationCode = getAuthzToken($_GET);
      $accessToken = getOAuthToken_curl(
                      $authorizationCode,
                     $applicationId,
                     $applicationSecret);
      # Use the OAuth token. For testing, we will simply write it to the log
      error_log('OAuth token: ' . $accessToken);
      error_log('Authorization succeeded!');
    }

  } catch (Exception $e) {
    echo $e->getMessage();
    error_log($e->getMessage());
  }

function drawButtons($accessToken, $action) {
  global $title, $description,$authorizationCode, $applicationId, $applicationSecret;
?>
  <html class='no-js' lang=''>
    <head>
        <meta charset='utf-8'>
        <title>'<?php echo $title; ?>'</title>
        <meta name='description' content='<?php echo $description; ?>'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <link rel='stylesheet' href='css/normalize.css'>
        <link rel='stylesheet' href='css/main.css'>
        <link rel='stylesheet' href='css/sqpaymentform.css'>
    </head>
    <body>
        <h1>Action: '<?php echo $action; ?>'</h1>
        Access token: '<?php echo $accessToken; ?>'
        <br/>
        <a href="request_token.php?func=renewOAuthToken&accessToken=<?php echo $accessToken;?>">Click here</a> to renew your access token
        <div/>
        <a href="request_token.php?func=revokeToken&accessToken=<?php echo $accessToken;?>">Click here</a> to revoke your access token

    </body>
<?php
}
  
# Verify the authz token returned by Square
function getAuthzToken($authorizationResponse) {

  # Extract the returned authorization code from the URL
  $authorizationCode = $authorizationResponse['code'];

  # If there is no authorization code, log the error and throw an exception
  if (!$authorizationCode) {
    error_log('Authorization failed!');
    throw new \Exception("Error Processing Request: Authorization failed!", 1);
  }

  return $authorizationCode ;
}
/*
  Exchange the authorization token for an Oauth token
 */
function getOAuthToken_curl($authorizationCode, $applicationId, $applicationSecret) {
  global $squareDomain, $accessToken;

  $oauthRequestHeaders = array(
    "Content-Type: application/json",
    "Accept: application/json",
    "Authorization: Client $applicationSecret"
  );
  $oauthRequestBody = array(
    'client_id' => $applicationId,
    'client_secret' => $applicationSecret,
    'code' => $authorizationCode
  );
  $encodedData = json_encode($oauthRequestBody);
    
  $ch = curl_init($squareDomain. '/oauth2/token');
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch , CURLOPT_HTTPHEADER, $oauthRequestHeaders );
  curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($ch);
  curl_close($ch);

  if ($response == null || !is_array($response) || !array_key_exists('access_token', $response)) {
    error_log('Get access token failed');
    $accessToken = 'Get access token failed';
} else {
    $accessToken = $response['access_token'];
   #$accessToken = json_decode($response)->access_token;

}


  drawButtons($accessToken, "Get access token");
  return $accessToken;
}

/*
Generates a POST request to the /oauth2/clients/{client ID}/access-token/renew endpoint
Requires the application Id (client Id), application secret, and the current access token
 */
function renewOAuthToken($accessToken) {
  global $squareDomain, $applicationId, $applicationSecret;

  # Headers to provide to OAuth API endpoints.
  $request_headers = array(
    "Content-Type: application/json",
    "Accept: application/json",
    "Authorization: Client $applicationSecret"
  );

  $oauthRequestBody = array(
      'access_token' => $accessToken,
  );
  $encodedData = json_encode($oauthRequestBody);

  $curl_handle = curl_init($squareDomain. '/oauth2/clients/'.$applicationId.'/access-token/renew');
  curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $encodedData);
  $request_headers[3] = "Content-Length: " . strlen($encodedData) ;
  curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "POST") ;
  curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $request_headers) ;
  curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1) ;
  $response = json_decode(curl_exec($curl_handle), true) ;
  curl_close($curl_handle) ;

  # If the exchange failed, log the error and throw an exception.
  $accessToken = "Renew failed";
  if ($response == null || !is_array($response) || !array_key_exists('access_token', $response)) {
      error_log('Renew token failed');
  } else {
      $accessToken = $response['access_token'];
  }
  drawButtons($accessToken, "Renew access token");
  return $accessToken;
}

/*  
Revokes the user/merchant access token.
Generates a POST operation to the /oauth2/revoke endpoint
Application secret, client ID, and the current access token must
be supplied in the POST.
*/
function revokeToken($accessToken) {
  global $squareDomain, $oauthRequestHeaders, $applicationId, $applicationSecret;

  $revokeRequestBody = array(
    'client_id' => $applicationId,
    'access_token' => $accessToken,
  );
  $encodedData = json_encode($revokeRequestBody);

  $request_headers = array(
    "Content-Type: application/json",
    "Accept: application/json",
    "Authorization: Client $applicationSecret"
  );
  array_push($request_headers, "Content-Length: " . strlen($encodedData)) ;

  $curl_handle = curl_init($squareDomain. '/oauth2/revoke');
  curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $encodedData);
  curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "POST") ;
  curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $request_headers) ;
  curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1) ;
  $response = json_decode(curl_exec($curl_handle), true) ;
  curl_close($curl_handle) ;

  # Prints "Success!" if you successfully revoke the token.
  if ($response == null || !is_array($response) || !array_key_exists('success', $response)) {
    drawButtons($accessToken, $response->body->message);
  } else {
    $accessToken = " "; 
    drawButtons($accessToken, "Access token revoked");
  }
}
?>