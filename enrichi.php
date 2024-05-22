<?php

// Définir le chemin vers le fichier JSON principal
$jsonFilePath = 'b2b_data.json';

// Gérer le téléchargement du fichier CSV
if (isset($_FILES['file'])) {
    $csvFilePath = $_FILES['file']['tmp_name'];

    // Convertir le fichier CSV en JSON
    $results = [];
    $handle = fopen($csvFilePath, 'r');
    while (($data = fgetcsv($handle)) !== FALSE) {
        $results[] = $data;
    }
    fclose($handle);

    // Lire le fichier JSON principal
    $mainData = json_decode(file_get_contents($jsonFilePath), true);

    // Fusionner les données CSV et JSON
    $updatedData = array_merge($mainData, $results);

    // Sauvegarder le résultat dans le fichier JSON principal
    file_put_contents($jsonFilePath, json_encode($updatedData));

    // Supprimer le fichier CSV après traitement
    unlink($csvFilePath);

    echo 'Le fichier CSV a été traité et les données ont été ajoutées au fichier JSON principal.';
} else {
    echo 'Veuillez télécharger un fichier CSV.';
}

?>
