<?php
require_once("controllers/Controller.php");
session_start();
date_default_timezone_set("Europe/Bratislava");

define('MYDIR','google-api-php-client--PHP8.0/');
require_once(MYDIR."vendor/autoload.php");
$controller = new Controller();

$redirect_uri = 'http://wt144.fei.stuba.sk/strankaZ3/oauth.php';

$client = new Google_Client();
$client->setAuthConfig('../../configs/credentials.json');
$client->setRedirectUri($redirect_uri);
$client->addScope("email");
$client->addScope("profile");

$service = new Google_Service_Oauth2($client);

if(isset($_GET['code'])){
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  $client->setAccessToken($token);
  $_SESSION['upload_token'] = $token;

  // redirect back to the example
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

// set the access token as part of the client
if (!empty($_SESSION['upload_token'])) {
  $client->setAccessToken($_SESSION['upload_token']);
  if ($client->isAccessTokenExpired()) {
    unset($_SESSION['upload_token']);
  }
} else {
  $authUrl = $client->createAuthUrl();
}

if ($client->getAccessToken()) {
    //Get user profile data from google
    $UserProfile = $service->userinfo->get();
    if(!empty($UserProfile)){
        $emailType = $controller->getEmailType($UserProfile['email']);
        if($emailType === false) {
            $controller->makeAccount($UserProfile['given_name'],$UserProfile['family_name'], $UserProfile['email'],"google", null, null,$UserProfile['id']);
            $id = $controller->getUserID($UserProfile['email']);
            $_SESSION["userId"] = $id;
            header("Location: user.html");
        }

        else if ($emailType === "google"){
            $id = $controller->getUserID($UserProfile['email']);
            $_SESSION["userId"] = $id;
            $controller->addAccess($id);
            header("Location: user.html");
        }
        else{
            header("Location: index.html");
            unset($_SESSION['upload_token']);
            $client->revokeToken();
        }
    }else{
        header("Location: index.html");
        unset($_SESSION['upload_token']);
        $client->revokeToken();
    }
} else {
    header("Location: index.html");
    unset($_SESSION['upload_token']);
    $client->revokeToken();

}


