<?php
require_once "controllers/Controller.php";

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Europe/Bratislava");
$controller = new Controller();
$response = array();


if ($_GET["do"] == "login"){
    require_once 'GoogleAuthenticator-master/PHPGangsta/GoogleAuthenticator.php';

    session_start();

    $email = trim($_POST["email"]);
    $qrCode = $_POST["qr"];

    try{
        $secret = $controller->getTwoFactorCode($email);

        $ga = new PHPGangsta_GoogleAuthenticator();
        $result = $ga->verifyCode($secret, $qrCode);

        if ($result != 1)
            throw new Exception("wrong-code");


        $id = $controller->getUserID($email);
        $_SESSION["userId"] = $id;
        $controller->addAccess($id);
        $response = array(
            "status" => "success",
            "error" => false,

        );
        echo json_encode($response);

    }catch (Exception $e){

        $response = array(
            "status" => "failed",
            "error" => true,
            "message" => $e->getMessage(),
        );

        echo json_encode($response);
    }
}if ($_GET["do"] == "checkLogin"){
    session_start();

    if (isset($_SESSION["userId"])){
        $response = array(
            "status" => "success",
            "error" => false,
            "login" => true,

        );
    echo json_encode($response);

    }else{
        $response = array(
            "status" => "failed",
            "error" => false,
            "login" => false,
        );

        echo json_encode($response);
    }
}else if($_GET["do"] == "check-login") {

    $email = trim($_POST["email"]);

    $password = $_POST["password"];


    try{
        if($controller->getUserID($email) == false)
            throw new Exception("noEmail");
        $isCorrect = $controller->doLogin($password, $email);
        $response = array(
            "status" => "success",
            "error" => false,
            "isCorrect" => $isCorrect,

        );
        echo json_encode($response);

    }catch (Exception $e){

        $response = array(
            "status" => "failed",
            "error" => true,
            "message" => $e->getMessage(),
        );

        echo json_encode($response);
    }

}else if($_GET["do"] == "account") {
    session_start();
    $id = $_SESSION["userId"];

    try{
        $user = $controller->getUser($id);
        $response = array(
            "status" => "success",
            "error" => false,
            "user" => $user,

        );
        echo json_encode($response);

    }catch (Exception $e){

        $response = array(
            "status" => "failed",
            "error" => true,
            "message" => $e->getMessage(),
            "id" => 5,
        );

        echo json_encode($response);
    }

}else if($_GET["do"] == "registration") {
    session_start();
    require_once 'GoogleAuthenticator-master/PHPGangsta/GoogleAuthenticator.php';
    $name = $_POST["name"];
    $surname = $_POST["surname"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $qrCode = $_POST["qr"];
    $secret = $_SESSION["twoFA-secret"];


    try{
        $ga = new PHPGangsta_GoogleAuthenticator();
        $result = $ga->verifyCode($secret, $qrCode);
        if ($result != 1)
            throw new Exception("wrong-code");

        $controller->makeAccount($name,$surname, $email,"other", $password, $secret,null);


        unset( $_SESSION["twoFA-secret"]);

        $response = array(
            "status" => "success",
            "error" => false,

        );
        echo json_encode($response);

    }catch (Exception $e){

        $response = array(
            "status" => "failed",
            "error" => true,
            "message" => $e->getMessage(),
        );

        echo json_encode($response);
    }

}else if($_GET["do"] == "checkEmail") {
    $email = $_POST["email"];
    try{
        $isExist = $controller->getUserID($email);

        $response = array(
            "status" => "success",
            "error" => false,
            "isUser"=> $isExist,
        );
        echo json_encode($response);

    }catch (Exception $e){

        $response = array(
            "status" => "failed",
            "error" => true,
            "message" => $e->getMessage(),
        );

        echo json_encode($response);
    }

}else if($_GET["do"] == "loginPast") {
    session_start();
    $id = $_SESSION["userId"];

    try{
        $logins = $controller->getAccess($id);
        $name = $controller->getName($id);
        $statisticOfLogs = $controller->getStatisticOfLogs();
        $response = array(
            "status" => "success",
            "error" => false,
            "logins" => $logins,
            "id" => $id,
            "name" =>$name,
            "statisticOfLogs" =>$statisticOfLogs,


        );
        echo json_encode($response);

    }catch (Exception $e){

        $response = array(
            "status" => "failed",
            "error" => true,
            "message" => $e->getMessage(),
            "id" => 5,
        );

        echo json_encode($response);
    }

}else if($_GET["do"] == "getQR") {
    session_start();
    require_once 'GoogleAuthenticator-master/PHPGangsta/GoogleAuthenticator.php';

    $websiteTitle = 'webte2-autentifikacia';
    try{
        $ga = new PHPGangsta_GoogleAuthenticator();

        $secret = $ga->createSecret();

        $qrCodeUrl = $ga->getQRCodeGoogleUrl($websiteTitle, $secret);

        $_SESSION["twoFA-secret"] = $secret;

        $response = array(
            "status" => "success",
            "error" => false,
            "qrUrl"=> $qrCodeUrl,

        );
        echo json_encode($response);

    }catch (Exception $e){

        $response = array(
            "status" => "failed",
            "error" => true,
            "message" => $e->getMessage(),
        );

        echo json_encode($response);
    }

}else if($_GET["do"] == "getGoogleLink") {
    session_start();

    try{
        define('MYDIR','google-api-php-client--PHP8.0/');
        require_once(MYDIR."vendor/autoload.php");

        $redirect_uri = 'http://wt144.fei.stuba.sk/strankaZ3/oauth.php';

        $client = new Google_Client();
        $client->setAuthConfig('../../configs/credentials.json');
        $client->setRedirectUri($redirect_uri);
        $client->addScope("email");
        $client->addScope("profile");
        $authUrl = $client->createAuthUrl(); $authUrl = $client->createAuthUrl();

        $response = array(
            "status" => "success",
            "error" => false,
            "googleLink"=> filter_var($authUrl, FILTER_SANITIZE_URL),

        );
        echo json_encode($response);

    }catch (Exception $e){

        $response = array(
            "status" => "failed",
            "error" => true,
            "message" => $e->getMessage(),
        );

        echo json_encode($response);
    }
}else if($_GET["do"] == "logOut") {
    session_start();

    try{
        define('MYDIR','google-api-php-client--PHP8.0/');
        require_once(MYDIR."vendor/autoload.php");

        $client = new Google_Client();
        $client->setAuthConfig('../../configs/credentials.json');
        unset($_SESSION["userId"]);

        //Unset token from session
        unset($_SESSION['upload_token']);
        $client->revokeToken();

        $response = array(
            "status" => "success",
            "error" => false,
        );
        echo json_encode($response);

    }catch (Exception $e){

        $response = array(
            "status" => "failed",
            "error" => true,
            "message" => $e->getMessage(),
        );

        echo json_encode($response);
    }
}else if($_GET["do"] == "session") {
    session_start();

    if (isset($_SESSION['userId'])){
        $response = array(
            "status" => "success",
            "isSet" => true,
        );
    echo json_encode($response);
    }
    else{

        $response = array(
            "status" => "failed",
            "isSet" => false,
        );

        echo json_encode($response);
    }

}