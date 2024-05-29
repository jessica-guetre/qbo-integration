<?php

require_once(__DIR__ . '/vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

session_start();

function processCode() {

    $config = include('config.php');

    // Configure DataService object
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $config['client_id'],
        'ClientSecret' =>  $config['client_secret'],
        'RedirectURI' => $config['oauth_redirect_uri'],
        'scope' => $config['oauth_scope'],
        'baseUrl' => "development"
    ));

    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper(); // Get the OAuth2LoginHelper instance
    $parseUrl = parseAuthRedirectUrl(htmlspecialchars_decode($_SERVER['QUERY_STRING'])); // Parse URL from redirect URI
    $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($parseUrl['code'], $parseUrl['realmId']);

    $dataService->updateOAuth2Token($accessToken); // Update the OAuth2Token of the DataService object
    $_SESSION['sessionAccessToken'] = $accessToken; // Set the access token in the session
}

function parseAuthRedirectUrl($url) {

    // Parse the redirect URL
    parse_str($url,$qsArray);
    return array(
        'code' => $qsArray['code'],
        'realmId' => $qsArray['realmId']
    );
}

$result = processCode();

?>