<?php
return array(
    'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
    'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
    'oauth_scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
    'oauth_redirect_uri' => 'http://localhost:8000/callback.php',
    'client_id' => '', // Replace with your QBO Client Id
    'client_secret' => '' //Replace with your QBO Client Secret
)

?>