<?php

require_once("../Datamart/class/Bdd.php");
require '../Datamart/vendor/autoload.php';
use Slim\Factory\AppFactory;
//$app = new App();
$app = AppFactory::create();
$postalCode = ['34000', '34001', '34002'];
// Définir la fonction pour générer le contenu du fichier CSV
function generateCsvContent($postalCode) {
    // Connexion à la base de données
    $db = new Bdd();
    $ddb = $db->connect();

    // Préparer la requête
    // Préparer la requête
        $query = $ddb->prepare('SELECT * FROM b2b WHERE cp IN (:code_postal)');

        // Remplacer le paramètre par un tableau
        $query->bindParam(':code_postal', $postalCode, PDO::PARAM_INT_ARRAY);

        // Exécuter la requête
        $query->execute();

    echo $query;

    // Ouvrir un fichier temporaire pour l'écriture
    $tmpFile = tmpfile();

    // Écrire les en-têtes
    fputcsv($tmpFile, ['Nom', 'Prénom', 'Email', 'Code postal']);

    // Écrire les données
    while ($row = $query->fetch()) {
        fputcsv($tmpFile, $row);
    }

    // Fermer le fichier
    fclose($tmpFile);

    return $tmpFile;
}

// Définir la route pour l'export CSV
$app->get('/export-csv/{postalCode}', function ($request, $response) {
    echo 'hhh';
    $postalCode = $request->getAttribute('postalCode');

    // Vérifier le paramètre de code postal
    if (!isValidPostalCode($postalCode)) {
        return $response->withStatus(400);
    }

    // Générer le contenu CSV
    $tmpFile = generateCsvContent($postalCode);

    // Définir les headers HTTP
    $response = $response->withHeader('Content-Type', 'text/csv')
                        ->withHeader('Content-Disposition', 'attachment; filename=export.csv');

    // Envoyer le fichier CSV
    return $response->withBody(stream_get_contents($tmpFile));

});

// Fonction pour valider le code postal (optionnelle)
function isValidPostalCode($postalCode) {
    // Implémentez votre logique de validation du code postal
    return true;
}
$app->run();
?>
