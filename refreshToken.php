<?php

require_once(__DIR__ . '/vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

session_start(); // Start PHP session

function refreshToken() {

    $config = include('config.php'); // Retrieve config details
    $accessToken = $_SESSION['sessionAccessToken']; // Retrieve the accessToken value from the session
    $dataService = DataService::Configure(array( // Create DataService instance
        'auth_mode' => 'oauth2',
        'ClientID' => $config['client_id'],
        'ClientSecret' =>  $config['client_secret'],
        'RedirectURI' => $config['oauth_redirect_uri'],
        'scope' => $config['oauth_scope'],
        'baseUrl' => "development"
    ));

    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper(); // Get the OAuth2LoginHelper instance
    $refreshedAccessTokenObj = $OAuth2LoginHelper->refreshToken();
    $error = $OAuth2LoginHelper->getLastError(); // Check for OAuth2 errors
    if ($error != null) {
        throw new Exception(
        "The Status code is: " . $error->getHttpStatusCode() . "\n" .
        "The Helper message is: " . $error->getOAuthHelperError() . "\n" .
        "The Response message is: " . $error->getResponseBody() . "\n");
    }

    $_SESSION['sessionAccessToken'] = $accessToken; // Set the access token in the session
    $dataService->updateOAuth2Token($accessToken); // Update the OAuth2Token of the DataService object
    $error = $dataService->getLastError(); // Check for API call errors
    if ($error != null) {
        throw new Exception(
        "The Status code is: " . $error->getHttpStatusCode() . "\n" .
        "The Helper message is: " . $error->getOAuthHelperError() . "\n" .
        "The Response message is: " . $error->getResponseBody() . "\n");
    }

    print_r($refreshedAccessTokenObj);
    return $refreshedAccessTokenObj;
}

$result = refreshToken();

?>