<?php

require_once(__DIR__ . '/vendor/autoload.php');
use QuickBooksOnline\API\Facades\Vendor;
use QuickBooksOnline\API\DataService\DataService;

session_start();

function updateVendor($vendorId, $newDetails) {

    // Check that a Vendor ID and at least one of the other fields is provided
    if (empty($vendorId)) {
        return "A Vendor ID is required.";
    } else if (empty($newDetails['DisplayName']) && empty($newDetails['PrimaryPhone']['FreeFormNumber']) && empty($newDetails['PrimaryEmailAddr']['Address']) && empty($newDetails['AcctNum'])) {
        return "At least one of name, phone, email, or account number must be provided.";
    }

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

    $vendor = $dataService->FindById('Vendor', $vendorId); // Retrieve the existing vendor object to update it
    if (!$vendor) {
        return "Vendor " . $vendorId . " not found.";
    }

    // Check if Vendor properties exist and update attributes
    foreach ($newDetails as $key => $value) {
        if (property_exists($vendor, $key)) {
            $vendor->$key = $value;
        } else {
            return "Vendor property " . $key . " does not exist";
        }
    }

    $updatedVendor = Vendor::update($vendor, $newDetails); // Create an updated vendor
    $resultingVendorUpdatedObj = $dataService->Update($updatedVendor); // Make an API call to update the vendor in QBO
    $error = $dataService->getLastError(); // Check for API call errors
    if ($error != null) {
        throw new Exception(
        "The Status code is: " . $error->getHttpStatusCode() . "\n" .
        "The Helper message is: " . $error->getOAuthHelperError() . "\n" .
        "The Response message is: " . $error->getResponseBody() . "\n");
    }

    return "Vendor updated successfully:\n" .
    "Vendor Name: " . $resultingVendorUpdatedObj->DisplayName . "\n" .
    "Phone: " . (isset($resultingVendorUpdatedObj->PrimaryPhone) ? $resultingVendorUpdatedObj->PrimaryPhone->FreeFormNumber : 'N/A') . "\n" .
    "Email: " . (isset($resultingVendorUpdatedObj->PrimaryEmailAddr) ? $resultingVendorUpdatedObj->PrimaryEmailAddr->Address : 'N/A') . "\n" .
    "Account Number: " . (isset($resultingVendorUpdatedObj->AcctNum) ? $resultingVendorUpdatedObj->AcctNum : 'N/A') . "\n";

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vendorId = $_POST['vendorId'];
    $newDetails = array();
    if (!empty($_POST['displayName'])) {
        $newDetails['DisplayName'] = $_POST['displayName'];
    }
    if (!empty($_POST['primaryPhone'])) {
        $newDetails['PrimaryPhone'] = ['FreeFormNumber' => $_POST['primaryPhone']];
    }
    if (!empty($_POST['primaryEmail'])) {
        $newDetails['PrimaryEmailAddr'] = ['Address' => $_POST['primaryEmail']];
    }
    if (!empty($_POST['accountNumber'])) {
        $newDetails['AcctNum'] = $_POST['accountNumber'];
    }

    $result = updateVendor($vendorId, $newDetails);
    echo $result;
}

?>