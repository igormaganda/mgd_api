<?php

require 'vendor/autoload.php';
require 'config/key.php';
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

// Vérification du token JWT dans le header de la requête
$headers = getallheaders();
$token = isset($headers["Authorization"]) ? str_replace("Bearer ", "", $headers["Authorization"]) : null;

if (!$token) {
    http_response_code(401);
    echo json_encode(["message" => "Autorisation requise"]);
    exit;
}

try {
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    session_start();

    // Accès aux données du token décodé
    $user_id = $decoded->uid;
    $email = $decoded->email;
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postData = file_get_contents('php://input');
        $data = json_decode($postData, true);

        // Vérification des données JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(["error" => "Données JSON invalides"]);
            exit;
        }

        // Nettoyage des chaînes
        foreach ($data as &$value) {
            if (is_string($value)) {
                $value = htmlspecialchars($value);
            }
        }

        // Traitement des différentes méthodes POST
        if (isset($data['method'])) {
            switch ($data['method']) {
                case 'count':
                    handleCountMethod($data);
                    break;
                case 'list':
                    handleListMethod($data);
                    break;
                case 'blacklist':
                    handleBlacklistMethod($data);
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['message' => 'Méthode non autorisée']);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Méthode non spécifiée"]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['message' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    http_response_code(401);
    if ($e instanceof \Firebase\JWT\ExpiredTokenException) {
        echo json_encode(["message" => "Jeton expiré"]);
    } else if ($e instanceof \Firebase\JWT\JWTException) {
        echo json_encode(["message" => "Jeton invalide"]);
    } else {
        echo json_encode(["message" => "Erreur de vérification du jeton ou Jeton expiré"]);
    }
    exit;
}

function handleCountMethod($data)
{
    $query = http_build_query(['data' => $data]);
    header("Location: count2.php?$query");
    exit;
}

function handleListMethod($data)
{
    $query = http_build_query(['data' => $data]);
    header("Location: list.php?$query");
    exit;
}

function handleBlacklistMethod($data)
{
    $query = http_build_query(['data' => $data]);
    header("Location: blacklist.php?$query");
    exit;
}


/*require 'vendor/autoload.php';
require 'config/key.php';
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

   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $postData = file_get_contents('php://input');
   // Decode les données JSON
   $data = json_decode($postData, true);

   // Vérification que les données JSON ont été correctement décodées
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["error" => "Données JSON invalides"]);
        exit;
    }

    // Nettoyage des chaînes pour éviter les injections SQL
    foreach ($data as &$value) {
        if (is_string($value)) {
            $value = htmlspecialchars($value);
        }
    }

        if(!isset($data['method'])){
            echo "eee";
        }else{
            if($data['method'] === 'count'){
                // Convertir le tableau en chaîne de requête
                $query = http_build_query(['data' => $data]);
                // Rediriger avec la chaîne de requête
            header("Location: count2.php?$query");
                exit;
                
            }else if($data['method'] === 'list'){
                // Convertir le tableau en chaîne de requête
                $query = http_build_query(['data' => $data]);
                // Rediriger avec la chaîne de requête
                header("Location: list.php?$query");
                exit;
            }else if($data['method'] === 'blacklist'){
                // Convertir le tableau en chaîne de requête
                $query = http_build_query(['data' => $data]);
                // Rediriger avec la chaîne de requête
                header("Location: blacklist.php?$query");
                exit;
            }
        }
    }else {

        //Gérer la méthode HTTP non valide
        http_response_code(405);
        $response = ['message' => 'Méthode non autorisée'];

        $jsonResponse = json_encode($response);

        // Définir le type de contenu pour la réponse JSON
        header('Content-Type: application/json');

        // Output erreur de reponse JSON
        echo $jsonResponse;
    }

   
} catch (Exception $e) {
    http_response_code(401);
// Gestion des erreurs de décodage
    if ($e instanceof \Firebase\JWT\ExpiredTokenException) {
        echo json_encode(["message" => "Jeton expiré"]);
    } else if ($e instanceof \Firebase\JWT\JWTException) {
        echo json_encode(["message" => "Jeton invalide"]);
    } else {
        echo json_encode(["message" => "Erreur de vérification du jeton ou Jeton expiré"]);
    }

    exit;
}*/




