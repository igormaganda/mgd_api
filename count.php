<?php
session_start();
require_once "Controllers/UserController.php";

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Utilisation de $user_id comme nécessaire
} else {
    // Gestion du cas où la variable de session n'est pas définie
    echo json_encode(["message"=>'La variable de session user_id n\'est pas définie.']);
}
header('Content-Type: application/json');
// Définir le point d'entrée
//$method = $_SERVER['REQUEST_METHOD'];

// Analyser la requête JSON
//$request = json_decode(file_get_contents('php://input'), true);
// Fonction pour générer un ID de session unique
function generateSessionId() {
    $date = date('Y-m-d-His');
    $randomHex = bin2hex(openssl_random_pseudo_bytes(4));
    return $date . '-' . $randomHex;
}
// Fonction de recherche
function search($data , $user_id) {
    $db = new UserController();
    $ddb = $db->connect();
   // if ($_SERVER['REQUEST_METHOD'] === 'POST'){
       // $postData = file_get_contents('php://input');
        // Decode the JSON data
       // $data = json_decode($postData, true);
        if(!isset($data['type'])){
            echo  json_encode(["message" => "Vous devez spécifié le type de campagne"]);
            exit;
         }
        //$type = $data['type'];
        $types = explode(",", $data['type']);
        $type = implode(",", $types);
        $sql ="SELECT DISTINCT count($type) FROM b2b WHERE emailpro NOT IN (SELECT email FROM blacklist_users WHERE id_user = $user_id )
            AND  blacklist IS NOT TRUE AND (statut IS NULL OR statut='SB')";
        $params = [];
        // Codes postaux
        /* if (isset($data['cp']) && !empty($data['cp'])) {
            $codes_postaux = explode(",", $data['cp']);
            if (isset($data['inclu_cp']) && in_array($data['inclu_cp'], ['false', 'no', 'non'])) {
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
        if (isset($data['cp']) && !empty($data['cp'])) {
            $codes_postaux = explode(",", $data['cp']);
            foreach ($codes_postaux as &$code_postal) {
                $code_postal = trim($code_postal);
              }
            //$implode = implode("','", $codes_postaux);
            
            if (isset($data['inclu_cp']) && ($data['inclu_cp'] === 'false' || $data['inclu_cp'] === false || $data['inclu_cp'] === 'no' || $data['inclu_cp'] === 'non')) {
                $sql .= "AND cp NOT IN ('".implode("','", ($codes_postaux))."')";
            
            }else{
                $sql .= "AND cp IN ('".implode("','", $codes_postaux)."')";
            }
        }
        // Ville
        if(isset($data['ville']) && !empty($data['ville'])){
            $villes = explode(",", $data['ville']);
            foreach ($villes as &$ville) {
                $ville = trim($ville);
              }
            
            if (isset($data['inclu_ville']) && $data['inclu_ville'] === 'false'|| $data['inclu_ville'] === 'no' || $data['inclu_ville'] === 'non') {
                $sql .= "AND ville NOT IN ('".implode("','", $villes)."')";
            } else {
                $sql .= "AND ville IN ('".implode("','", $villes)."')";
            }
        }

        // Secteur d'activite
        if(isset($data['cat']) && !empty($data['cat'])){
            $cat = explode(",", $data['cat']);
            foreach ($cat as &$cat_) {
                $cat_ = trim($cat_);
            }
            if (isset($data['inclu_cat']) && $data['inclu_cat'] === 'false'|| $data['inclu_cat'] === 'no' || $data['inclu_cat'] === 'non') {
                $sql .= "AND cat NOT IN ('".implode("','", $cat)."')";
                
            }else{
                $sql .= "AND cat IN ('".implode("','", $cat)."')";
            }
        }
        // Naf
        if(isset($data['naf']) && !empty($data['naf'])){
            $naf = explode(",", $data['naf']);
            foreach ($naf as &$naf_) {
                $naf_ = trim($naf_);
              }
            if (isset($data['inclu_naf']) && $data['inclu_naf'] === 'false'|| $data['inclu_naf'] === 'no' || $data['inclu_naf'] === 'non') {
            $sql .= "AND naf NOT IN ('".implode("','", $naf)."')";
            }else
            $sql .= "AND naf IN ('".implode("','", $naf)."')";
        }
        // Forme juridique
        if(isset($data['forme']) && !empty($data['forme'])){
            $forme = explode(",", $data['forme']);
            foreach ($forme as &$form) {
                $form = trim($form);
            }
            if (isset($data['inclu_forme']) && $data['inclu_forme'] === 'false'|| $data['inclu_forme'] === 'no' || $data['inclu_forme'] === 'non') {
                $sql .= "AND forme NOT IN ('".implode("','", $forme)."')";
            }else{
                $sql .= "AND forme IN ('".implode("','", $forme)."')";
            }
        }
        // Chiffre d'affaire
        if(isset($data['ca']) && !empty($data['ca'])){
            if (!isset($data['ca_max']) && !isset($data['ca_min'])){
                
                $ca = explode(",", $data['ca']);
                foreach ($ca as &$ca_) {
                    $ca_ = trim($ca_);
                }
                if (isset($data['inclu_ca']) && $data['inclu_ca'] === 'false'|| $data['inclu_ca'] === 'no' || $data['inclu_ca'] === 'non') {
                    $sql .= "AND ca NOT IN ('".implode("','", $ca)."')";
                }else{
                $sql .= "AND ca IN ('".implode("','", $ca)."')";
                }
            }   
        } 
        if (isset($data['ca_max']) || isset($data['ca_min'])){
            if($data['ca_var'] === 'inf') {
            
                $sql .= "AND (ca::integer < ".$data['ca_max'].")";
            }else if($data['ca_var'] === 'sup') {
                
                    $sql .= "AND (ca > ".$data['ca_min'].")";
            }else if($data['ca_var'] === 'entre'){
                $sql .= "AND ca::integer >= ".$data['ca_min']." AND ca::integer <= ".$data['ca_max'];
            }else{
                $sql .= "AND ca::integer = ".$data['ca_max'];

            }
                
        }
    // Effectif
        if(isset($data['eff']) || !empty($data['eff'])){
            if (!isset($data['eff_valeur_max']) && !isset($data['eff_valeur_min'])){
                $effectif = explode(",", $data['effectif']);
                foreach ($effectif as &$effectif_) {
                    $effectif_ = trim($effectif_);
                }
                if (isset($data['inclu_eff']) && $data['inclu_eff'] === 'false'|| $data['inclu_eff'] === 'no' || $data['inclu_eff'] === 'non') {
                $sql .= "AND effectif NOT IN ('".implode("','", $effectif)."')";
                }else{
                $sql .= "AND effectif IN ('".implode("','", $effectif)."')";
                }
            }
        }

        if (isset($data['eff_valeur_max']) || isset($data['eff_valeur_min'])){
            if($data['eff_var'] === 'inf') {
                
                $sql .= "AND (effectif::integer > ".$data['eff_valeur_max'].")";
            }else if($data['eff_var'] === 'sup') {
                
                    $sql .= "AND (effectif::integer > ".$data['eff_valeur_max'].")";
            }else if($data['eff_var'] === 'entre'){
                $sql .= "AND effectif::integer >= ".$data['eff_valeur_min']." AND effectif::integer <= ".$data['eff_valeur_max'];
            }else{
                $sql .= "AND effectif::integer = ".$data['eff_valeur_max'];

            }
                
        }
      
         // Environnement
        if(isset($terms['env']) && !empty($terms['env'])){
            $env = explode(",", $terms['env']);
            foreach ($env as &$environnement) {
                $environnement = trim($environnement);
            }
            $sql .= "AND env IN ('".implode("','", $env)."')";
        }
        // Fonction
        if(isset($terms['fonc']) && !empty($terms['fonc'])){
            $fonc = explode(",", $terms['fonc']);
            foreach ($fonc as &$fonction) {
                $fonction = trim($fonction);
            }
            $sql .= "AND fonction IN ('".implode("','", $fonc)."')";
        }
        // Gender
        if(isset($terms['gend']) && !empty($terms['gend'])){
            $gend = explode(",", $terms['gend']);
            foreach ($gend as &$gender) {
                $gender = trim($gender);
            }
            $sql .= "AND gender IN ('".implode("','", $gend)."')";
        }
        
        ///echo $sql;
        $stmt = $ddb->prepare($sql);

        //echo $params;

        // Exécution de la requête
        //$stmt->execute($params);
            $stmt->execute();

        // Récupération des résultats de la recherche
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($results) {
            $rowCount = $stmt->rowCount();
             
            // echo "resultat superieur a 0";
                // Récupérer les données et les stocker dans un tableau
                // Formater la réponse en JSON
                $response = [
                    'status' => 200,
                    'session_id' => generateSessionId(),
                    'method' => "count",
                    //'campaign_type' => $campaignType,
                    'total' => $results[0]['count'],
                   // 'data' => $results
                ];
            } else {
            //  echo "pas de resultat";
                // Aucun résultat trouvé
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
            $response =['message' => 'Une erreur est survenue'];
            exit;
        }
    //return json_encode($results);
/*}else {
        // Si la méthode HTTP n'est pas GET, renvoie un message d'erreur
        http_response_code(405);
        $response = ['message' => 'Méthode non autorisée'];
    }*/

       // Encoder la réponse en JSON
       $jsonResponse = json_encode($response);

       // Définir le type de contenu et retourner la réponse
   
       return $jsonResponse;

}
// Exécution de la recherche
$data = $_GET['data'];
$results = search($data, $user_id);

// Renvoi des résultats au format JSON
echo $results;
