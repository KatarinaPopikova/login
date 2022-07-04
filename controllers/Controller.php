<?php
require_once "helpers/Database.php";
require_once "models/User.php";

class Controller
{
    private $conn;

    /**
     * Controller constructor.
     * @param $conn
     */
    public function __construct(){
        $database = new Database();
        $this->conn = $database->getConn();
    }

    public function doLogin($password, $email){
        $hashPassword = $this->getPassword($email);
        return $this->checkPassword($hashPassword, $password);

    }

    public function getPassword($email){
        $stmt = $this->conn->prepare("SELECT password FROM ACCOUNT 
                                    WHERE user_id = (SELECT id FROM USER WHERE email = :email)");
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);

        $stmt->execute();
        return  $stmt->fetchColumn();

    }

    public function checkPassword($hashPassword, $password): bool
    {
        return password_verify ($password , $hashPassword );
    }

    public function getTwoFactorCode($email){
        $stmt = $this->conn->prepare("SELECT twofactor_code FROM ACCOUNT 
                                    WHERE user_id = (SELECT id FROM USER WHERE email = :email)");
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);

        $stmt->execute();
        return  $stmt->fetchColumn();
    }

    public function makeAccount($name, $surname, $email,$type, $password, $secret,$googleId){
        try{
        $userId= $this->addUser($name, $surname, $email);
        $this->addAccount($userId, $password, $type, $secret, $googleId);
        if ($type !="other")
            $this->addAccess($userId);

        }catch (PDOException $e){
            throw $e;
        }
    }

    public function addAccount($userId, $password, $type, $secret, $googleId){
        $stmt = $this->conn->prepare("INSERT INTO ACCOUNT (user_id, password,type,google_id, twofactor_code)
                                        VALUES (:user_id, :password, :type, :googleId, :twofactor_code)");
        $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
        $stmt->bindValue(":password", $password, PDO::PARAM_STR);
        $stmt->bindValue(":type", $type, PDO::PARAM_STR);
        $stmt->bindValue(":twofactor_code", $secret, PDO::PARAM_STR);
        $stmt->bindValue(":googleId", $googleId, PDO::PARAM_STR);


        try{
            $stmt->execute();
            return $this->conn->lastInsertId();
        }catch (PDOException $e){
            throw $e;
        }
    }

    public function addUser($name, $surname, $email)
    {
        $stmt = $this->conn->prepare("INSERT INTO USER (name, surname,email)
                                        VALUES (:name, :surname, :email)");
        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        $stmt->bindValue(":surname", $surname, PDO::PARAM_STR);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);

        try{
            $stmt->execute();
            return $this->conn->lastInsertId();
        }catch (PDOException $e){
            throw $e;
        }
    }

    public function getUserID($email){
        $stmt = $this->conn->prepare("SELECT id FROM USER WHERE email = :email");
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);

        $stmt->execute();
        return  $stmt->fetchColumn();
    }

    public function getEmailType($email){
        $stmt = $this->conn->prepare("SELECT type FROM ACCOUNT WHERE user_id = (SELECT id FROM USER WHERE email = :email)");
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);

        $stmt->execute();
        return  $stmt->fetchColumn();
    }

    public function getUser($id){
        $stmt = $this->conn->prepare("SELECT USER.name, USER.surname, USER.email, ACCOUNT.type
                                        FROM ACCOUNT
                                        INNER JOIN USER ON ACCOUNT.user_id = USER.id
                                        WHERE ACCOUNT.user_id = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, "User");
        return $stmt->fetch();

    }

    public function getAccess($id){
        $stmt = $this->conn->prepare("SELECT timestamp
                                        FROM ACCESS
                                        WHERE account_id = (
                                        SELECT id FROM ACCOUNT WHERE user_id = :user_id)");
        $stmt->bindValue(":user_id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }

    public function getName($id){
        $stmt = $this->conn->prepare("SELECT USER.name, USER.surname FROM USER 
                                        WHERE id = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function addAccess($id){
        $stmt = $this->conn->prepare("INSERT INTO ACCESS (account_id, timestamp)
                            VALUES ((SELECT id FROM ACCOUNT WHERE user_id = :id),:timestamp )");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(':timestamp', date("Y-m-d H:i:s", time()), PDO::PARAM_STR);
        try{
            $stmt->execute();
            return $this->conn->lastInsertId();
        }catch (PDOException $e){
            throw $e;
        }
    }

    public function getStatisticOfLogs(){
        $stmt = $this->conn->prepare("SELECT ACCOUNT.type, COUNT(*) AS counts FROM ACCOUNT 
                                        INNER JOIN ACCESS on ACCOUNT.id = ACCESS.account_id
                                        GROUP BY ACCOUNT.type
                                        ORDER BY ACCOUNT.type");

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }
}