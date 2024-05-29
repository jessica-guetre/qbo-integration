<?php

require_once(__DIR__ . '/vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

session_start();

function getVendor() {

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

        if (!isset($_SESSION) || !isset($_SESSION['sessionAccessToken'])) {
            http_response_code(400);
            throw new Exception("Session is not set, or session access token is missing.");
        }
        $accessToken = $_SESSION['sessionAccessToken']; // Retrieve the access token in the session
        $dataService->updateOAuth2Token($accessToken); // Update the OAuth2Token of the DataService object

        $vendors = $dataService->Query("SELECT DisplayName, PrimaryPhone, PrimaryEmailAddr, AcctNum FROM Vendor"); // Perform query for Vendor data
        $error = $dataService->getLastError(); // Check for API call errors
        if ($error != null) {
            http_response_code(500);
            throw new Exception(
            "The Status code is: " . $error->getHttpStatusCode() . "\n" .
            "The Helper message is: " . $error->getOAuthHelperError() . "\n" .
            "The Response message is: " . $error->getResponseBody() . "\n");
        }

        $result = "";
        foreach ($vendors as $vendor) { // Output Vendor data
            $result .= "Id: " . $vendor->Id . "\n";
            $result .= "Name: " . $vendor->DisplayName . "\n";
            if (isset($vendor->PrimaryPhone->FreeFormNumber)) {
                $result .= "Phone: " . $vendor->PrimaryPhone->FreeFormNumber . "\n";
            }
            if (isset($vendor->PrimaryEmailAddr->Address)) {
                $result .= "Email: " . $vendor->PrimaryEmailAddr->Address . "\n";
            }
            if (isset($vendor->AcctNum)) {
                $result .= "Account Number: " . $vendor->AcctNum . "\n";
            }
            $result .= "\n";
        }

        echo $result;
        return $result;

    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(["error" => "An error occurred while getting vendor information. Details contained within log."]);
    }
}

$result = getVendor();

?>