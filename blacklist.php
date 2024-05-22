<?php
session_start();
require_once "Controllers/UserController.php";
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Utilisation de $user_id comme nécessaire
} else {
    // Gestion du cas où la variable de session n'est pas définie
    echo 'La variable de session user_id n\'est pas définie.';
}
// Définir les en-têtes
header('Content-Type: application/json');

// Ajouter les contacts à la blacklist

// Envoyer la réponse

    // Decode the JSON data
    $data = $_GET['data'];
    //$item ="AND cp IN '".implode("','", $items)."'";
    // Access object properties
    $method = $data['method'];
    $campaignType = $data['type'];
    $blacklist = $data['item'];
    $UserController = new UserController();
    $response= $UserController->ajouterBlacklist($user_id, $blacklist);
    echo $response;

?>
