<?php

require_once(__DIR__ . '/vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

session_start();

function getVendor() {

    $config = include('config.php'); // Retrieve config details
    $dataService = DataService::Configure(array( // Create DataService instance
        'auth_mode' => 'oauth2',
        'ClientID' => $config['client_id'],
        'ClientSecret' =>  $config['client_secret'],
        'RedirectURI' => $config['oauth_redirect_uri'],
        'scope' => $config['oauth_scope'],
        'baseUrl' => "development"
    ));

    $accessToken = $_SESSION['sessionAccessToken']; // Retrieve the access token in the session
    $dataService->updateOAuth2Token($accessToken); // Update the OAuth2Token of the DataService object

    $vendors = $dataService->Query("SELECT DisplayName, PrimaryPhone, PrimaryEmailAddr, AcctNum FROM Vendor"); // Perform query for Vendor data
    $error = $dataService->getLastError(); // Check for API call errors
    if ($error != null) {
        throw new Exception(
        "The Status code is: " . $error->getHttpStatusCode() . "\n" .
        "The Helper message is: " . $error->getOAuthHelperError() . "\n" .
        "The Response message is: " . $error->getResponseBody() . "\n");
    }

    foreach ($vendors as $vendor) { // Output Vendor data
        echo "Id: " . $vendor->Id . "\n";
        echo "Name: " . $vendor->DisplayName . "\n";
        if (isset($vendor->PrimaryPhone->FreeFormNumber)) {
            echo "Phone: " . $vendor->PrimaryPhone->FreeFormNumber . "\n";
        }
        if (isset($vendor->PrimaryEmailAddr->Address)) {
            echo "Email: " . $vendor->PrimaryEmailAddr->Address . "\n";
        }
        if (isset($vendor->AcctNum)) {
            echo "Account Number: " . $vendor->AcctNum . "\n";
        }
        echo "\n";
    }

    return $vendors;
}

$result = getVendor();

?>