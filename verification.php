<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
$headers = getallheaders();


// Clé secrète (la même que celle utilisée pour la génération de jetons)
$key = 'azerty1234';

// Récupérer le jeton JWT de l'en-tête Authorization de la requête
$jwt = $headers['Authorization'];
$jwt = preg_replace('/Bearer\s*/', '', $jwt); // Supprimer le mot-clé "Bearer

echo $jwt;
try {
    $dba = new Bdd();
    $ddb = $dba->connect();
    // Décoder et vérifier le jeton JWT
    $decoded = JWT::decode($jwt, $key, ['HS256']);

    // Extraire les informations de l'utilisateur du payload du jeton
    $utilisateurId = $decoded->uid;
    $roles = $decoded->roles;

    // Vérifier si l'utilisateur existe dans la base de données
      // Se connecter à la base de données (remplacez par votre connexion réelle)
      $db = new PDO('mysql:host=localhost;dbname=votre_base_de_donnees', 'votre_utilisateur', 'votre_mot_de_passe');

      // Préparer la requête pour vérifier si l'utilisateur existe
      $stmt = $ddb->prepare('SELECT * FROM utilisateurs WHERE id = :id');
      $stmt->bindParam(':id', $utilisateurId);
      $stmt->execute();
  
      // Vérifier si l'utilisateur existe dans la base de données
      $utilisateur = $stmt->fetch();
      if (!$utilisateur) {
          // L'utilisateur n'existe pas, renvoyer une erreur appropriée
          echo json_encode(['message' => 'Utilisateur introuvable']);
          http_response_code(401); // Non autorisé
          exit();
      }

    // Si l'utilisateur est valide, autoriser l'accès à la ressource
    // ... (implémentez la logique d'autorisation)

} catch (UnexpectedValueException $e) {
    // Le jeton est invalide, renvoyer une erreur appropriée
    echo json_encode(['message' => 'Jeton invalide']);
    http_response_code(401); // Non autorisé
    exit();
}
?>

