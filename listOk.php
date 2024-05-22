<?php
require_once("Controllers/UserController.php");
require_once("../Datamart/class/Bdd.php");
require_once("index.php");

// Fonction pour générer un ID de session unique
function generateSessionId() {
    $date = date('Y-m-d-His');
    $randomHex = bin2hex(openssl_random_pseudo_bytes(4));
    return $date . '-' . $randomHex;
}

// Fonction de recherche
function search($terms) {
    $dba = new Bdd();
    $ddb = $dba->connect();
    //$ddb =new UserController();
    if ($_SERVER['REQUEST_METHOD'] === 'GET'){
       /* authenticateUser($_SERVER['HTTP_LOGIN'], $_SERVER['HTTP_APIKEY']);

        if (!checkHeaders() || !authenticateUser()) {
            // Envoyer une erreur 401 Unauthorized
            header('HTTP/1.1 401 Unauthorized');
            echo 'Accès non autorisé: Veuillez fournir des informations d\'identification valides.';
            exit;
        }*/
        if(isset($_GET['type'])){
            $types = explode(",", $_GET['type']);
            $type = implode("','", $type);

            $sql = "SELECT DISTINCT  $type FROM b2b WHERE email NOT IN (SELECT email FROM blacklist_users WHERE id_user = $user_id )
            AND  blacklist IS NOT TRUE AND (statut IS NULL OR statut='SB')";




        }else{
            $sql = "SELECT DISTINCT emailpro FROM b2b WHERE email NOT IN (SELECT email FROM blacklist_users WHERE id_user = $user_id ) AND blacklist IS NOT TRUE AND (statut IS NULL OR statut='SB')";
        }

        /*pour la blacklist du client :email NOT IN (SELECT email_contact FROM blacklist WHERE id_client = :idClient)');*/

        $params = [];
        // Codes postaux
        /* if (isset($_GET['cp']) && !empty($_GET['cp'])) {
            $codes_postaux = explode(",", $_GET['cp']);
            if (isset($_GET['inclu_cp']) && in_array($_GET['inclu_cp'], ['false', 'no', 'non'])) {
                $sql .= " AND cp NOT IN (" . implode(',', array_fill(0, count($codes_postaux), '?')) . ")";
                $params = array_merge($params, $codes_postaux);
            } else {
                echo "ici";
                $sql .= " AND cp IN (" . implode(',', array_fill(0, count($codes_postaux), '?')) . ")";
                $params = array_merge($params, $codes_postaux);
                echo $params;
            }
        }*/
        // code postaux
        if (isset($_GET['cp']) && !empty($_GET['cp'])) {
            $codes_postaux = explode(",", $_GET['cp']);
            //$implode = implode("','", $codes_postaux);
            
            if (isset($_GET['inclu_cp']) && $_GET['inclu_cp'] === 'false'|| $_GET['inclu_cp'] === 'no' || $_GET['inclu_cp'] === 'non') {
            }else{
                $sql .= "AND cp IN ('".implode("','", $codes_postaux)."')";
            }
        }
        // Ville
        if(isset($_GET['ville']) && !empty($_GET['ville'])){
            $ville = explode(",", $_GET['ville']);
            
            if (isset($_GET['inclu_ville']) && $_GET['inclu_ville'] === 'false'|| $_GET['inclu_ville'] === 'no' || $_GET['inclu_ville'] === 'non') {
                $sql .= "AND ville NOT IN ('".implode("','", $ville)."')";
            } else {
                $sql .= "AND ville IN ('".implode("','", $ville)."')";
            }
        }

        // Secteur d'activite
        if(isset($_GET['cat']) && !empty($_GET['cat'])){
            $cat = explode(",", $_GET['cat']);
            if (isset($_GET['inclu_cat']) && $_GET['inclu_cat'] === 'false'|| $_GET['inclu_cat'] === 'no' || $_GET['inclu_cat'] === 'non') {
                $sql .= "AND cat NOT IN ('".implode("','", $cat)."')";
                
            }else{
                $sql .= "AND cat IN ('".implode("','", $cat)."')";
            }
        }
        // Naf
        if(isset($_GET['naf']) && !empty($_GET['naf'])){
            $naf = explode(",", $_GET['naf']);
            if (isset($_GET['inclu_naf']) && $_GET['inclu_naf'] === 'false'|| $_GET['inclu_naf'] === 'no' || $_GET['inclu_naf'] === 'non') {
            $sql .= "AND naf NOT IN ('".implode("','", $naf)."')";
            }else
            $sql .= "AND naf IN ('".implode("','", $naf)."')";
        }
        // Forme juridique
        if(isset($_GET['forme']) && !empty($_GET['forme'])){
            $forme = explode(",", $_GET['forme']);
            if (isset($_GET['inclu_forme']) && $_GET['inclu_forme'] === 'false'|| $_GET['inclu_forme'] === 'no' || $_GET['inclu_forme'] === 'non') {
                $sql .= "AND forme NOT IN ('".implode("','", $forme)."')";
            }else{
                $sql .= "AND forme IN ('".implode("','", $forme)."')";
            }
        }
        // Chiffre d'affaire
        if(isset($_GET['ca']) && !empty($_GET['ca'])){
            if (!isset($_GET['ca_valeur_max']) && !isset($_GET['ca_valeur_min'])){
                
                $ca = explode(",", $_GET['ca']);
                if (isset($_GET['inclu_ca']) && $_GET['inclu_ca'] === 'false'|| $_GET['inclu_ca'] === 'no' || $_GET['inclu_ca'] === 'non') {
                    $sql .= "AND ca NOT IN ('".implode("','", $ca)."')";
                }else{
                $sql .= "AND ca IN ('".implode("','", $ca)."')";
                }
            }   
        } 
        if (isset($_GET['ca_valeur_max']) || isset($_GET['ca_valeur_min'])){
            if($_GET['ca_var'] === 'inf') {
            
                $sql .= "AND (ca > ".$_GET['ca_valeur_max'].")";
            }else if($_GET['ca_var'] === 'sup') {
                
                    $sql .= "AND (ca > ".$_GET['ca_valeur_max'].")";
            }else if($_GET['ca_var'] === 'entre'){
                $sql .= "AND ca::integer >= ".$_GET['ca_valeur_min']." AND ca::integer <= ".$_GET['ca_valeur_max'];
            }else{
                $sql .= "AND ca = ".$_GET['ca_valeur_max'];

            }
                
        }
        // Effectif
        if(isset($_GET['eff']) || !empty($_GET['eff'])){
            if (!isset($_GET['eff_valeur_max']) && !isset($_GET['eff_valeur_min'])){
                $effectif = explode(",", $_GET['effectif']);

                if (isset($_GET['inclu_eff']) && $_GET['inclu_eff'] === 'false'|| $_GET['inclu_eff'] === 'no' || $_GET['inclu_eff'] === 'non') {
                $sql .= "AND effectif NOT IN ('".implode("','", $effectif)."')";
                }else{
                $sql .= "AND effectif IN ('".implode("','", $effectif)."')";
                }
            }
        }

        if (isset($_GET['eff_valeur_max']) || isset($_GET['eff_valeur_min'])){
            if($_GET['eff_var'] === 'inf') {
                
                $sql .= "AND (effectif > ".$_GET['eff_valeur_max'].")";
            }else if($_GET['eff_var'] === 'sup') {
                
                    $sql .= "AND (effectif > ".$_GET['eff_valeur_max'].")";
            }else if($_GET['eff_var'] === 'entre'){
                $sql .= "AND effectif::integer >= ".$_GET['eff_valeur_min']." AND effectif::integer <= ".$_GET['eff_valeur_max'];
            }else{
                $sql .= "AND effectif = ".$_GET['eff_valeur_max'];

            }
                
        }
        if (isset($_GET['limit']) && !empty($_GET['limit'])){
            $sql .= "LIMIT ".''.$_GET['limit'];
        }
        //echo $sql;
        $stmt = $ddb->prepare($sql);

        //echo $params;

        // Exécution de la requête
        //$stmt->execute($params);
            $stmt->execute();

        // Récupération des résultats de la recherche
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($results) {

            /*const PRICE_PER_EMAIL = 0.001;
            const PRICE_PER_PHONE = 0.0015;
            const PRICE_PER_EMAIL_AND_PHONE =  0.0025;
            const PRICE_PER_CODE_POSTAL = 0.009; // Prix pour afficher le code postal*/

            $username = $_SESSION['email'];
            $locationType =$_GET['type'] ;
            $limit = $_GET['limit'] ;
            $totalPrice = $limit * 0.5;
                // Récupérer les données et les stocker dans un tableau
                // Formater la réponse en JSON
                $response = [
                    'status' => 200,
                    'session_id' => generateSessionId(),
                // 'method' => $method,
                    //'campaign_type' => $campaignType,
                    'total' => count($results),
                    'data' => $results
                ];
            $logApiUsage = new UserController();
            $logApiUsage->logApiUsage($username, $totalPrice, $locationType, $limit);
            
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Aucune donnée trouvée .'
                ];

            }

            // Fermer la connexion à la base de données
            //$db->close($connection);
        // Gérer les erreurs
        if (!$results) {
            http_response_code(500);
            $response =[
                'success' => false,
                'message' => 'Une erreur est survenue'
            ];
            exit;
        }
        //return json_encode($results);
    }else {
        // Si la méthode HTTP n'est pas GET, renvoie un message d'erreur
        http_response_code(405);
        $response = ['message' => 'Méthode non autorisée'];
    }

       // Encoder la réponse en JSON
       $jsonResponse = json_encode($response);

       // Définir le type de contenu et retourner la réponse
       // header('Content-Type: application/json');
       //header('Content-Disposition: attachment; filename=response.json');
       // header('Content-Disposition: inline; filename=response.json');
       // Générer le nom du fichier
        $filename = 'fichier_' . date('YmdHis') . '.json';

        // Enregistrer la réponse JSON dans un fichier temporaire
        $tempFile = tempnam(sys_get_temp_dir(), 'json');
        file_put_contents($tempFile, $jsonResponse);

        // Forcer le téléchargement du fichier temporaire
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Length: ' . filesize($tempFile));

    readfile($tempFile);

    // Supprimer le fichier temporaire
    unlink($tempFile);
   
}
// Exécution de la recherche
$results = search($_GET);

// Renvoi des résultats au format JSON
echo $results;
?>
