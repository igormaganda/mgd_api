<?php
require_once("../Datamart/class/Bdd.php");
// Paramétres de connexion à la base de données

try {
  // Création de la connexion PDO
  $db = new Bdd();
  $conn = $db->connect(); 
  // Requête SQL pour créer la table utilisateur
  $sql = "CREATE TABLE IF NOT EXISTS users (
      id INT PRIMARY KEY,
      nom VARCHAR(255)  NULL,
      prenom VARCHAR(255)  NULL,
      email VARCHAR(255) UNIQUE NULL,
      password VARCHAR(255) NOT NULL,
      date_inscription TIMESTAMP
  )";

  // Préparation de la requête
  $stmt = $conn->prepare($sql);

  // Exécution de la requête
  $stmt->execute();

  echo "Table utilisateur créée avec succès";
} catch(PDOException $e) {
  echo "Erreur lors de la création de la table utilisateur : " . $e->getMessage();
}

// Fermeture de la connexion (automatique avec garbage collector)

?>
