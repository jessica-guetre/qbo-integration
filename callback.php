<?php

require_once(__DIR__ . '/vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

session_start();

function processCode() {

    try {

        $config = include('config.php'); // Retrieve config details
        $dataService = DataService::Configure(array( // Create DataService instance
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
        $error = $OAuth2LoginHelper->getLastError(); // Check for OAuth2 errors
        if ($error != null) {
            http_response_code(500);
            throw new Exception(
            "The Status code is: " . $error->getHttpStatusCode() . "\n" .
            "The Helper message is: " . $error->getOAuthHelperError() . "\n" .
            "The Response message is: " . $error->getResponseBody() . "\n");
        }

        $_SESSION['sessionAccessToken'] = $accessToken; // Set the access token in the session
        $dataService->updateOAuth2Token($accessToken); // Update the OAuth2Token of the DataService object
        $error = $dataService->getLastError(); // Check for API call errors
        if ($error != null) {
            http_response_code(500);
            throw new Exception(
            "The Status code is: " . $error->getHttpStatusCode() . "\n" .
            "The Helper message is: " . $error->getOAuthHelperError() . "\n" .
            "The Response message is: " . $error->getResponseBody() . "\n");
        }

    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(["error" => "An error while logging in. Details contained within log."]);
    }

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