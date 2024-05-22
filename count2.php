<?php
session_start();
// Include necessary files
require_once("Controllers/UserController.php");

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Gestion du cas où la variable de session n'est pas définie
    echo 'La variable de session user_id n\'est pas définie.';
}

    $search = new UserController();
    $data = $_GET['data'];
   
    // Perform search based on GET parameters
    $results = $search->search($data, $user_id);
    //var_dump($results);
    // Process search results if successful
    if ($results) {
     
        if(isset($data['limit'])){
            $limit = $data['limit'];
            $totalPrice = $limit * 0.5;

        }else{
            $limit = $results['count'];
            $totalPrice = $limit * 0.5;
        }


        // Prepare response data
        $response = [
            'status' => 200,
            'session_id' => $search->generateSessionId(),
            'total' => $results['count'],
        ];
    } else {
        // Set error response
        http_response_code(500);
        $response = [
            'success' => false,
            'message' => 'Une erreur est survenue'
        ];
    }
    header('Content-Type: application/json');

    // Encode response to JSON
    $jsonResponse = json_encode($response);
    echo $jsonResponse;
