<?php

require_once(__DIR__ . '/vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

session_start(); // Start PHP session

function refreshToken() {

    $config = include('config.php');

    $accessToken = $_SESSION['sessionAccessToken']; // Retrieve the accessToken value from the session

    // Configure DataService object
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $config['client_id'],
        'ClientSecret' =>  $config['client_secret'],
        'RedirectURI' => $config['oauth_redirect_uri'],
        'baseUrl' => "development",
        'refreshTokenKey' => $accessToken->getRefreshToken(),
        'QBORealmID' => "The Company ID which the app wants to access",
    ));

    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper(); // Get the OAuth2LoginHelper instance
    $refreshedAccessTokenObj = $OAuth2LoginHelper->refreshToken();

    $dataService->updateOAuth2Token($refreshedAccessTokenObj); // Update the OAuth2Token of the refreshed token
    $_SESSION['sessionAccessToken'] = $refreshedAccessTokenObj; // Set the access token in the session

    print_r($refreshedAccessTokenObj);
    return $refreshedAccessTokenObj;
}

$result = refreshToken();

?>