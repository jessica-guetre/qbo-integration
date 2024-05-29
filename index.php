<?php

require_once(__DIR__ . '/vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

session_start();

$config = include('config.php');

// Check that client_id and client_secret are set
if (empty($config['client_id']) || empty($config['client_secret'])) {
    die("Error: client_id and client_secret must be passed through the command line.\n");
}

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
$authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL(); // Get authorization URL
$_SESSION['authUrl'] = $authUrl; // Set the authorization URL in the session

// Set the access token in the session if it exists
if (isset($_SESSION['sessionAccessToken'])) {
    $accessToken = $_SESSION['sessionAccessToken'];

    $accessTokenJson = array('token_type' => 'bearer',
        'access_token' => $accessToken->getAccessToken(),
        'refresh_token' => $accessToken->getRefreshToken(),
        'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
        'expires_in' => $accessToken->getAccessTokenExpiresAt()
    );

    $dataService->updateOAuth2Token($accessToken); // Update the OAuth2Token of the DataService object
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper(); // Get the OAuth2LoginHelper instance
}

?>

<!DOCTYPE html>
<html>
<head>
    <link href="styles.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="https://attachments.developer.intuit.com/appcard-f21813f1-eee1-4a9d-9e92-45f3c58832f3.png">
    <script>

        var url = '<?php echo $authUrl; ?>';

        // Define OAuthCode class for handling OAuth login in a popup window
        var OAuthCode = function(url) {

            this.loginPopup = function (parameter) {
                this.loginPopupUri(parameter);
            }

            // Launch popup window for OAuth login
            this.loginPopupUri = function (parameter) {
                var parameters = "location=1,width=800,height=650";
                parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;

                var win = window.open(url, 'connectPopup', parameters);
                var pollOAuth = window.setInterval(function () {
                    try {
                        // Check if authorization code is present in URL
                        if (win.document.URL.indexOf("code") != -1) {
                            window.clearInterval(pollOAuth);
                            win.close();
                            location.reload(); // Reload page after successful login
                        }
                    } catch (e) {
                        console.log(e)
                    }
                }, 100);
            }
        }

        // Define apiCall class for calls to QBO API
        var apiCall = function() {

            // AJAX request to get vendor information
            this.getVendor = function() {
                $.ajax({
                    type: "GET",
                    url: "getVendor.php",
                }).done(function( msg ) {
                    $('#getVendor').html( msg );
                });
            }

            // AJAX request to update vendor information
            this.updateVendor = function() {
                $.ajax({
                    type: "POST",
                    url: "updateVendor.php",
                    data: {
                        vendorId: $('#vendorId').val(),
                        displayName: $('#displayName').val(),
                        primaryPhone: $('#primaryPhone').val(),
                        primaryEmail: $('#primaryEmail').val(),
                        accountNumber: $('#accountNumber').val()
                    }
                }).done(function(msg) {
                    $('#updateVendor').html(msg);
                    $('#vendorForm')[0].reset();
                });
            }

            // AJAX request to refresh the access token
            this.refreshToken = function() {
                $.ajax({
                    type: "POST",
                    url: "refreshToken.php",
                }).done(function( msg ) {

                });
            }
        }

        // Create instances of OAuthCode and apiCall classes
        var oAuth = new OAuthCode(url);
        var apiCallInstance = new apiCall();

    </script>
</head>

<div class="container">
    <hr>
    <div class="well text-center">
        <h1>Take-Home Assignment</h1>
        <h2>Integration with Cloud-Based ERP</h2>
    </div>

    <h3>Connect to QuickBooks</h3>
    <p>If there is no access token or the access token is invalid, click the <b>Connect</b> button below.</p>
    <pre id="accessToken"><?php 
        if (isset($accessTokenJson)) {
            echo json_encode($accessTokenJson, JSON_PRETTY_PRINT);
        } else {
            echo "No Access Token Generated Yet";
        }
    ?></pre>
    <button type="button" class="btn btn-success btn-custom" onclick="oAuth.loginPopup()">Connect</button>
    <hr />

    <h3>Update Vendor Info</h3>
    <p>A <b>Vendor ID</b> is required. At least one of the remaining fields must be filled.</p>
    <form id="vendorForm">
        <div class="form-group">
            <label for="vendorId">Vendor ID:</label>
            <input type="text" class="form-control" id="vendorId" placeholder="Enter Vendor ID">
        </div>
        <div class="form-group">
            <label for="displayName">Display Name:</label>
            <input type="text" class="form-control" id="displayName" placeholder="Enter Display Name">
        </div>
        <div class="form-group">
            <label for="primaryPhone">Primary Phone:</label>
            <input type="text" class="form-control" id="primaryPhone" placeholder="Enter Primary Phone">
        </div>
        <div class="form-group">
            <label for="primaryEmail">Primary Email:</label>
            <input type="email" class="form-control" id="primaryEmail" placeholder="Enter Primary Email">
        </div>
        <div class="form-group">
            <label for="accountNumber">Account Number:</label>
            <input type="text" class="form-control" id="accountNumber" placeholder="Enter Account Number">
        </div>
        <pre id="updateVendor"></pre>
        <button type="button" class="btn btn-success btn-custom" onclick="apiCallInstance.updateVendor()">Update Info</button>
    </form>
    <hr>

    <h3>Get Vendor Info</h3>
    <pre id="getVendor"></pre>
    <button type="button" class="btn btn-success btn-custom" onclick="apiCallInstance.getVendor()">Get Info</button>    <hr />

</div>
</body>
</html>