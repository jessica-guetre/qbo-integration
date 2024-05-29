<?php

require_once(__DIR__ . '/vendor/autoload.php');
use QuickBooksOnline\API\Facades\Vendor;
use QuickBooksOnline\API\DataService\DataService;

session_start();

function updateVendor($vendorId, $newDetails) {

    // Check if Vendor ID is provided
    if (empty($vendorId)) {
        return "Error: Vendor ID is required.";
    }

    // Check to make sure at least one of the non Vendor ID fields is provided
    if (empty($newDetails['DisplayName']) && empty($newDetails['PrimaryPhone']['FreeFormNumber']) && empty($newDetails['PrimaryEmailAddr']['Address']) && empty($newDetails['AcctNum'])) {
        return "Error: At least one of phone, email, or account number must be provided.";
    }

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

    $accessToken = $_SESSION['sessionAccessToken'];
    $dataService->updateOAuth2Token($accessToken); // Update the OAuth2Token of the DataService object

    // Retrieve the existing vendor object to update it
    // Check to make sure vendor exists
    $vendor = $dataService->FindById('Vendor', $vendorId);
    if (!$vendor) {
        return "Error: Vendor not found.";
    }
    $updatedVendor = Vendor::update($vendor, $newDetails);

    // Check if Vendor properties exist and update attributes
    foreach ($newDetails as $key => $value) {
        if (property_exists($vendor, $key)) {
            $vendor->$key = $value;
        }
    }

    // Update the vendor in QBO
    $resultingVendorUpdatedObj = $dataService->Update($updatedVendor);
    $error = $dataService->getLastError();

    if ($error) {
        return "The Status code is: " . $error->getHttpStatusCode() . "\n" .
               "The Helper message is: " . $error->getOAuthHelperError() . "\n" .
               "The Response message is: " . $error->getResponseBody() . "\n";
    } else {
        return "Vendor updated successfully:\n" .
               "Vendor Name: " . $resultingVendorUpdatedObj->DisplayName . "\n" .
               "Phone: " . (isset($resultingVendorUpdatedObj->PrimaryPhone) ? $resultingVendorUpdatedObj->PrimaryPhone->FreeFormNumber : 'N/A') . "\n" .
               "Email: " . (isset($resultingVendorUpdatedObj->PrimaryEmailAddr) ? $resultingVendorUpdatedObj->PrimaryEmailAddr->Address : 'N/A') . "\n" .
               "Account Number: " . (isset($resultingVendorUpdatedObj->AcctNum) ? $resultingVendorUpdatedObj->AcctNum : 'N/A') . "\n";
    }
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