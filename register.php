<?php
   // require_once("../Datamart/class/Bdd.php");
    require_once("Controllers/UserController.php");

    $headers = getallheaders();

   // $login = $headers['Login'];
   // $password = $headers['ApiKey'];

    try {
        $login = $headers['Login'];
         $password = $headers['ApiKey'];
        /*$login = "kao.igor@gmail.com";
        $password = "maganda";*/
       // echo $login;
       // $db = $con->connect();
    
        $userController = new UserController();
        $token = $userController->register($login, $password);
        echo json_encode($token);
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["message" => $e->getMessage()]);
    }



?>