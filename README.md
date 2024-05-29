## Take-Home Assignment: Integration with Cloud-Based ERP

This is a simple application that integrates with QuickBooks Online to retrieve and update vendor information from a sandbox environment. 

### Requirements

PHP and Composer are required to run this application.

### Installation

Follow [these steps](https://developer.intuit.com/app/developer/qbo/docs/get-started/start-developing-your-app
) to create a developer account and sandbox company with QuickBooks Online. From the 'Keys & OAuth' page, locate your 'Client Id' and 'Client Secret' and add 'http://localhost:8000/callback.php' as a redirect URI.

Clone this repository.
```
git clone https://github.com/jessica-guetre/qbo-integration.git
cd qbo-integration
```

Install package dependencies.
```
composer install
```

Add your 'Client Id' and 'Client Secret' to `config.php` file.
```php
<?php
return array(
    'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
    'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
    'client_id' => '',
    'client_secret' => '',
    'oauth_scope' => 'com.intuit.quickbooks.accounting openid profile email phone address', // Replace with your QBO Client Id
    'oauth_redirect_uri' => 'http://localhost:8000/callback.php', // Replace with your QBO Client Secret
);
?>
```

### Usage

Start the application on localhost from your commandline.
```
php -S localhost:8000
```

Navigate to http://localhost:8000/ in your browser. Press 'Connect' and log in using your QuickBooks Online account information. Select the company you'd like to use.

Once logged in, vendor information can be accessed or updated. 

**Get Vendor Info** 

Press 'Get Info' to get the Id for each vendor within the company
![](https://github.com/jessica-guetre/qbo-integration/blob/main/images/getVendorEmpty.png)

The given name, primary phone number, primary email address and account number for each vendor will be displayed if they exist.
![](https://github.com/jessica-guetre/qbo-integration/blob/main/images/getVendor.png)

**Update Vendor Info**

Fill in the Id for the vendor you'd like to change.
![](https://github.com/jessica-guetre/qbo-integration/blob/main/images/updateVendorEmpty.png)

At least one of the other fields, given name, primary phone number, primary email address or account number are required.
![](https://github.com/jessica-guetre/qbo-integration/blob/main/images/updateVendorFilled.png)

Press 'Update Info' once the correct information is filled in.
![](https://github.com/jessica-guetre/qbo-integration/blob/main/images/updateVendorComplete.png)

### Aknowledgements

This applications extends the [Intuit PHP demo app](https://github.com/IntuitDeveloper/HelloWorld-PHP). This includes the retrieval and update of vendor information, as well as greater coverage for error handling and logging.
