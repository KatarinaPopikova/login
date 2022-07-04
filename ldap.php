<?php
require_once "controllers/Controller.php";

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Europe/Bratislava");
$controller = new Controller();
$response = array();

$email = trim($_POST["email"]);
$password = $_POST["password"];
$mailName = strstr($email, '@', true);
$type = strstr($email, '@');
$dn = 'ou=People, DC=stuba, DC=sk';
$ldaprdn = "uid=$mailName, $dn";
$ldapconn = ldap_connect("ldap.stuba.sk");

try {
    if ($type != "@stuba.sk")
        throw new Exception("notStuba");
    if (!ldap_connect())
        throw new Exception("ldap connect failed");
    $set = ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    $ldapbind = ldap_bind($ldapconn, $ldaprdn, $password);
    if ($ldapbind) {
        $results = ldap_search($ldapconn, $dn, "uid=" . $mailName, array("givenname", "surname"), 0, 1);
        $info = ldap_get_entries($ldapconn, $results);
        $emailType = $controller->getEmailType($email);
        if ($emailType === false) {
            $controller->makeAccount($info[0]['givenname'][0], $info[0]['sn'][0], $email, 'ldap', null, null, null);
            $id = $controller->getUserID($email);
            session_start();
            $_SESSION["userId"] = $id;
        } else if ($emailType === "ldap") {
            $id = $controller->getUserID($email);
            session_start();
            $_SESSION["userId"] = $id;
            $controller->addAccess($id);
        } else {
            throw new Exception("AnotherType");
        }

        $response = array(
            "status" => "success",
            "error" => false,
            "ldapStatus" => $ldapbind,
        );
        echo json_encode($response);
    }
    else{
        throw new Exception("InvalidData");
    }
}
catch
    (Exception $exception){
        $response = array(
            "status" => "failed",
            "error" => true,
            "message" => $exception->getMessage(),
        );
        echo json_encode($response);
    }

ldap_unbind($ldapconn);
