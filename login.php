<?php
    require_once "Controllers/UserController.php";

    header('Content-Type: application/json');
    $headers = getallheaders();
   
   /* $con = new Bdd();
    $db = $con->connect();*/

    
    try {
        $login = $headers['Login'];
        $password = $headers['ApiKey'];
        $userController = new UserController();
        $token = $userController->login($login, $password);
        echo json_encode($token);
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["message" => $e->getMessage()]);
    }


?>