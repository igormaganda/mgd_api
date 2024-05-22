<?php

// Configuration

require_once '../Datamart/vendor/autoload.php';

$app = new \Slim\App();

// Définition des prix unitaires
const PRICE_PER_EMAIL = 0.001;
const PRICE_PER_PHONE = 0.0015;
const PRICE_PER_EMAIL_AND_PHONE = 0.0025;
const PRICE_PER_CODE_POSTAL = 0.009;

// Chargement du fichier JSON principal
$jsonFilePath = __DIR__ . '/b2b_data.json';
$data = json_decode(file_get_contents($jsonFilePath), true);

// Middleware d'authentification
$auth = function ($request, $response, $next) {
    $authToken = $request->getHeaderLine('Authorization');

    if (!$authToken) {
        return $response->withStatus(401)->json(['error' => "Token d'authentification manquant"]);
    }

    // Vérification du token dans la base de données
    $user = getUserByAuthToken($authToken);

    if (!$user) {
        return $response->withStatus(401)->json(['error' => "Token d'authentification invalide"]);
    }

    $request = $request->withAttribute('user', $user);

    return $next($request, $response);
};

// Fonction pour récupérer l'utilisateur à partir du token
function getUserByAuthToken($authToken) {
    // Connexion à la base de données
    $db = new PDO('pgsql:host=localhost;dbname=b2b_43cu', 'b2b_43cu_user', 'A85E0Nat3o5YN1fGopfOgZkuhnwOsxvc');

    // Requête pour récupérer l'utilisateur
    $query = 'SELECT * FROM users WHERE api_token = :token';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':token', $authToken);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user;
}

// Route pour la recherche de contacts
$app->get('/recherche', $auth, function ($request, $response) {
    $user = $request->getAttribute('user');

    $params = $request->getQueryParams();

    $filteredData = $data;

    // Filtrage des données
    foreach ($params as $key => $value) {
        if (empty($value)) {
            continue;
        }

        switch ($key) {
            case 'raison_sociale':
                $filteredData = array_filter($filteredData, function ($item) use ($value) {
                    return $item['Raison sociale'] === $value;
                });
                break;
            // ... autres cas pour les autres champs du formulaire
        }
    }

    // Calcul du prix total
    $totalPrice = 0;
    $showEmail = isset($params['show_email']) && $params['show_email'] === 'true';
    $showPhone = isset($params['show_phone']) && $params['show_phone'] === 'true';
    $showCodePostal = isset($params['show_code_postal']) && $params['show_code_postal'] === 'true';

    foreach ($filteredData as $item) {
        if ($showEmail) {
            $totalPrice += PRICE_PER_EMAIL;
        }
        if ($showPhone) {
            $totalPrice += PRICE_PER_PHONE;
        }
        if ($showCodePostal) {
            $totalPrice += PRICE_PER_CODE_POSTAL;
        }
    }

    // Limite de résultats
    $limit = isset($params['limit']) ? (int) $params['limit'] : null;
    if ($limit !== null) {
        $filteredData = array_slice($filteredData, 0, $limit);
        $totalPrice *= $limit;
    }

    // Génération du rapport CSV
    $csvOutput = generateCsv($filteredData);

    // Enregistrement de l'historique d'utilisation
    logApiUsage($user['username'], $user['api_token'], $totalPrice, implode(', ', array_filter([
        $showEmail ? 'Email' : null,
        $showPhone ? 'Phone' : null,
        $showCodePostal ? 'Code Postal' : null,
    ])), $limit);

    return $response;
})
?>