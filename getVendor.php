<?php

require_once(__DIR__ . '/vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

session_start();

function getVendor() {

    $config = include('config.php');

    // Create DataService instance
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $config['client_id'],
        'ClientSecret' =>  $config['client_secret'],
        'RedirectURI' => $config['oauth_redirect_uri'],
        'scope' => $config['oauth_scope'],
        'baseUrl' => "development"
    ));

    $accessToken = $_SESSION['sessionAccessToken']; // Retrieve the access token in the session
    $dataService->updateOAuth2Token($accessToken); // Update the OAuth2Token of the DataService object

    $vendors = $dataService->Query("SELECT DisplayName, PrimaryPhone, PrimaryEmailAddr, AcctNum FROM Vendor");
    $error = $dataService->getLastError();

    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    } else {
        foreach ($vendors as $vendor) {
            echo "Id: " . $vendor->Id . "\n";
            echo "Name: " . $vendor->DisplayName . "\n";
            if (isset($vendor->PrimaryPhone)) {
                echo "Phone: " . $vendor->PrimaryPhone->FreeFormNumber . "\n";
            }
            if (isset($vendor->PrimaryEmailAddr)) {
                echo "Email: " . $vendor->PrimaryEmailAddr->Address . "\n";
            }
            echo "\n";
        }
    }

    return $vendors;
}

$result = getVendor();

?>