<?php
session_start();

require_once("Controllers/UserController.php");

// Vérification si l'utilisateur est authentifié
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'La variable de session user_id n\'est pas définie.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$search = new UserController();
$data = isset($_GET['data']) ? $_GET['data'] : null;

// Vérification des données GET
if (!$data) {
    echo json_encode(['error' => 'Paramètre data manquant.']);
    exit;
}

// Effectuer une recherche basée sur les paramètres GET
$results = $search->search($data, $user_id);

if ($results) {
    $limit = isset($data['limit']) ? $data['limit'] : $results['count'];
    $totalPrice = $limit * 0.5;

    // Log API usage
    $search->logApiUsage($_SESSION['email'], $totalPrice, $_GET['type'] ?? 'unknown', $limit);

     // Générer le contenu JSON
     $jsonContent = json_encode($results['result']);

     // Générer un nom de fichier temporaire unique
     $tempFileName = 'temp_export_' . uniqid() . '.json';
 
     // Chemin complet du fichier temporaire sur le serveur
     $tempFilePath = 'https://api22.mgd-crm.com/files/' . $tempFileName;
 
     // Écrire le contenu JSON dans le fichier temporaire
     file_put_contents($tempFilePath, $jsonContent);
 
     // Créer le lien de téléchargement
     $downloadLink = 'https://api22.mgd-crm.com/files/' . $tempFileName;
 

    // Préparer les données de réponse
    $response = [
        'status' => 200,
        'session_id' => $search->generateSessionId(),
        'total' => $results['count'],
        'download_link' => $downloadLink, // Ajout du lien de téléchargement
        'data' => $results['result']
    ];
} else {
    // Réponse en cas d'erreur
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Une erreur est survenue'
    ];
}
// header('Content-Type: application/json');
// echo json_encode($response);

// if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
    ob_start('ob_gzhandler');
} else {
   
    ob_start();
}
header('Content-Type: application/json');
echo json_encode($response);
ob_end_flush();
/*session_start();

require_once("Controllers/UserController.php");

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Gestion du cas où la variable de session n'est pas définie
    echo 'La variable de session user_id n\'est pas définie.';
}

    $search = new UserController();
    $data = $_GET['data'];
   
    // Effectuer une recherche basée sur les paramètres GET
    $results = $search->search($data, $user_id);
    //var_dump($results);
    // Traiter les résultats de la recherche en cas de succès
    if ($results) {
     
        if(isset($data['limit'])){
            $limit = $data['limit'];
            $totalPrice = $limit * 0.5;

        }else{
            $limit = $results['count'];
            $totalPrice = $limit * 0.5;
        }

          // Log API usage
       $search->logApiUsage($_SESSION['email'], $totalPrice, $_GET['type'], $limit);

        // Préparer les données de réponse
        $response = [
            'status' => 200,
            'session_id' => $search->generateSessionId(),
            'total' => $results['count'],
            'data' => $results['result'],
        ];
    } else {
        // <réponse en cas d'erreur
        http_response_code(500);
        $response = [
            'success' => false,
            'message' => 'Une erreur est survenue'
        ];
    }
    header('Content-Type: application/json');

    // Encode son
    $jsonResponse = json_encode($response);
    echo $jsonResponse;
*/