<?php
require '../vendor/autoload.php';
require '../config/key.php';
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;


header('Content-Type: application/json');
// Vérification du token JWT dans le header de la requête
$headers = getallheaders();
//echo $headers["Authorization"];
$token = isset($headers["Authorization"]) ? str_replace("Bearer ", "", $headers["Authorization"]) : null;

if (!$token) {
    http_response_code(401);
    echo json_encode(["message" => "Autorisation requise"]);
    exit;
}

try {
   $decoded = JWT::decode($token, new Key($secret_key, 'HS256')); // Utilisation de ["HS256"] au lieu de "HS256"
    session_start();
   // Accès aux données du token décodé
   $user_id = $decoded->uid;
   $email = $decoded->email;
   $_SESSION['user_id'] = $user_id;
   $_SESSION['email'] = $email;

   
} catch (Exception $e) {
    http_response_code(401);
// Gestion des erreurs de décodage
    if ($e instanceof \Firebase\JWT\ExpiredTokenException) {
        echo json_encode(["message" => "Jeton expiré"]);
    } else if ($e instanceof \Firebase\JWT\JWTException) {
        echo json_encode(["message" => "Jeton invalide"]);
    } else {
        echo json_encode(["message" => "Erreur de vérification du jeton"]);
    }

    exit;
}




