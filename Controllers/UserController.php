<?php
require_once __DIR__ . '/../vendor/autoload.php';
// require_once __DIR__ . '../loadEnv.php';
require_once __DIR__ . '/../loadEnv.php';


// Charger les variables d'environnement
//loadEnv(__DIR__ . '/.env');

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;


class UserController {

    private $db;
    private $PARAM_hote;
    private $PARAM_port;
    private $PARAM_nom_bd;
    private $PARAM_utilisateur;
    private $PARAM_mot_passe;

    public function __construct() {
      
        $this->PARAM_hote        ='localhost';
        $this->PARAM_port        ='5432';
        $this->PARAM_nom_bd      ='datamart3';
        $this->PARAM_utilisateur ='postgres';
        $this->PARAM_mot_passe   ='P057G435';

        $db = new PDO(
            'pgsql:host='	.$this->PARAM_hote
            .';port='		.$this->PARAM_port
            .';dbname='		.$this->PARAM_nom_bd
            .';user='		.$this->PARAM_utilisateur
            .';password='	.$this->PARAM_mot_passe
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->db = $db;
        
    }
    public function connect() {
        $db = new PDO(
            'pgsql:host='	.$this->PARAM_hote
            .';port='		.$this->PARAM_port
            .';dbname='		.$this->PARAM_nom_bd
            .';user='		.$this->PARAM_utilisateur
            .';password='	.$this->PARAM_mot_passe
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $db;
    }

    public function login($username, $password) {
        require 'vendor/autoload.php';
        $secret_key = getenv('JWT_SECRET_KEY');
        // Valider les entrées
        // if (empty($username) || empty($password)) {
        //     throw new Exception("Identifiants invalides");
        // }
        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        $user = $this->getUserByUsername($username);
     // if (!$user || !password_verify($password, $user->getPassword())) {
            if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Identifiants invalides");
        }
        $payload = [
            //'iss' => 'votre_domaine', // Emetteur du jeton
            'iat' => time(), // Heure d'émission du jeton
            'exp' => time() + 14400, // Date d'expiration du jeton (1 heure)
            'uid' => $user['id'], // ID utilisateur
            "email" => $user['email'],
            // Rôles utilisateur
        ];
     //   [  "id" => $user['id'],"nom" => $user['nom'], "email" => $user['email']]

        // Générer le token JWT
        $jwt = JWT::encode($payload, $secret_key, 'HS256');

        return [ 
                 "success" => true,
                 "token" => $jwt
                ];
       //return $user['password'];
    }

    private function getUserByUsername($username) {
        // Requête SQL pour récupérer l'utilisateur par son nom d'utilisateur
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1,$username);
        $stmt->execute();

        $result = $stmt->fetch();
      //  echo $result[0];
       /* if (!$result) {
            return null;
        }*/

        //return new User($result['id'], $result['email'], $result['password']);
        return $result;
    }
    public function register($login, $password) {
        require 'vendor/autoload.php';
        $secret_key = getenv('JWT_SECRET_KEY');

        // Valider l'e-mail
        if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Adresse e-mail invalide");
        }
        // Valider le mot de passe (exemple: au moins 8 caractères)
        if (strlen($password) < 8) {
            throw new Exception("Le mot de passe doit contenir au moins 8 caractères");
        }
        // Vérifier si l'utilisateur existe déjà
        $userName = $this->getUserByUsername($login);
       
        //echo $userName[0];
       if ($this->getUserByUsername($login)) {
            throw new Exception("Cet utilisateur existe déjà");
        }

        // Hasher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Generate a unique ID using a combination of time and random numbers
        $uniqueID = uniqid(mt_rand(), false); // Génère une ID unique alphanumérique
        $numericID = preg_replace('/[^0-9]/', '', $uniqueID); // Filtre uniquement les chiffres

        try{
            // Insérer le nouvel utilisateur dans la base de données
            $sql = "INSERT INTO users (email, password, date_inscription) VALUES (?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $login);
            $stmt->bindParam(2, $hashedPassword);
            $stmt->execute();

            // Récupérer l'ID de l'utilisateur nouvellement créé
            $userId = $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'inscription de l'utilisateur: " . $e->getMessage());
        }
    
        // Générer le token JWT
        $payload = [
            //'iss' => 'votre_domaine', // Emetteur du jeton
            'iat' => time(), // Heure d'émission du jeton
            'exp' => time() + 14400, // Date d'expiration du jeton (1 heure)
            'uid' => $userId, // ID utilisateur
            "email" => $login,
            // Rôles utilisateur
        ];
       // echo $userId;
        $jwt = JWT::encode($payload, $secret_key, 'HS256');

        return ["token" => $jwt];
      
    }

    function logApiUsage($username, $totalPrice, $locationType, $limit){
        $currentDate = new DateTime();

        $formattedDate = $currentDate->format('Y-m-d H:i:s'); // PostgreSQL timestamp format
      
        // Using RETURNING * to retrieve generated values on insert
        $query ="INSERT INTO api_history (email, usage_date, total_price, location_type, api_limit) VALUES (?, ?, ?, ?,?) RETURNING *";
      
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1,$username);
        $stmt->bindParam(2,$formattedDate);
        $stmt->bindParam(3,$totalPrice);
        $stmt->bindParam(4,$locationType);
        $stmt->bindParam(5,$limit);
        $result = $stmt->execute();
        /*if($result) {
            try{
            // Retrieve generated values on insert
            $insertedRow = $result->rows[0];
            echo "API History inserted successfully: ", json_encode($insertedRow), "\n";
            }catch(Exception $err) {
                echo "Error recording API history: ", $err->getMessage(), "\n";
                // You can handle this error as needed
            }
          
        };*/
      
        // Also send total_price to the order_api table
        $orderQuery ="INSERT INTO order_api (email, order_date, total_price) VALUES (?, ?, ?) RETURNING *";

        $stmt2 = $this->db->prepare($orderQuery);
        $stmt2->bindParam(1,$username);
        $stmt2->bindParam(2,$formattedDate);
        $stmt2->bindParam(3,$totalPrice);
        $result1 = $stmt2->execute();

        /*if($result1) {
            try{
                // Retrieve generated values on insert
                $insertedRow = $result1->rows[0];
                echo "API Order inserted successfully: ", json_encode($insertedRow), "\n";
            }catch(Exception $e) {
            echo "Error recording API order: ", $e->getMessage(), "\n";
            // You can handle this error as needed
          }
        }*/
    }

    function ApiHistory(){
        // Requête SQL pour afficher toutes les informations de la table api_history
        $sql = "SELECT * FROM api_history";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result =$stmt->fetchAll(PDO::FETCH_ASSOC);
       // var_dump($result);
        return json_encode([
            "count" => count($result),
            "api_history" => $result]);
    }
    function orderApi(){
        // Requête SQL pour afficher toutes les informations de la table api_history
        $sql = "SELECT * FROM order_api";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result =$stmt->fetchAll(PDO::FETCH_ASSOC);
       // var_dump($result);
        return json_encode([
            "count" => count($result),
            "order_api" => $result]);
    }

    function getAllUsers(){
        // Requête SQL pour afficher toutes les informations de la table api_history
        $sql = "SELECT * FROM users";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result =$stmt->fetchAll(PDO::FETCH_ASSOC);
       // var_dump($result);
        return json_encode([
            "count" => count($result),
            "users" => $result]);
    }

    function ajouterBlacklist($idClient, $emailContacts) {
        // Préparer la requête
        /* $stmt = $this->db->prepare("INSERT INTO blacklist (id_client, email_contact) VALUES (?,?)");
        
        // Lier les paramètres
        $stmt->bindParam(1, $idClient);
        $stmt->bindParam(2, $emailContact);*/


        // Itérer sur les emails
        //  foreach ($emailContacts as $emailContact) {
            // Exécuter la requête pour chaque email
        // echo $emailContact;
            //$stmt->execute();
        // }
        $emails = explode(',', $emailContacts);

        // Prepare SQL query to insert each email into the database
        $sql = "INSERT INTO blacklist_users (id_user, email, created_at) VALUES (?,?, NOW())";
        $stmt = $this->db->prepare($sql);

        // Bind parameter for email
        $stmt->bindParam(1, $idClient);

        // Iterate through each email address
        foreach ($emails as $email) {
            // Trim whitespace from email
            $email = trim($email);

            // Validate email format (optional)
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Bind the email to the prepared statement
                $stmt->bindParam(2, $email);

                // Execute the query to insert the email
                $stmt->execute();

                // Check for insert errors
                if ($stmt->error) {
                   $response= ['success' => false, 'message' => "Error inserting email: " . $stmt->error];
                } else {
                   $response= ['success' => true, 'message' => "Contacts ajoutés à la blacklist."];
                }
            } else {
                $response= ['success' => false, 'message' => "Invalid email format: '$email'"];
            }
        }

        // Close the prepared statement and database connection
        //    $stmt->close();
        //    $db->close();


        return json_encode($response);

    }

    function generateSessionId() {
        $date = date('Y-m-d-His');
        $randomHex = bin2hex(openssl_random_pseudo_bytes(4));
        return $date . '-' . $randomHex;
    }
    function search($terms, $user_id) {
        if (!isset($terms['type'])) {
            echo json_encode(["message" => "Vous devez spécifier le type de campagne par exemple type=emailpro"]);
            exit;
        }
    
        // Initialisation de la requête SQL
        $sql = "SELECT * FROM b2b_130k limit 50000";
        $params = [];
    
        // Type de campagne
        $types = is_array($terms['type']) ? $terms['type'] : explode(",", $terms['type']);
        $types = array_map('trim', $types);
        $type = implode(",", $types);
    
        // Préparer les filtres dynamiques
        $filters = [
            'region' => "LEFT(cp, 2)",
            'departement' => "LEFT(cp, 2)",
            'cp' => "cp",
            'commune' => "communes",
            'ville' => "ville",
            'categeorie' => "LEFT(naf, 2)",
            'sous_categeorie' => "LEFT(naf, 3)",
            'naf' => "naf",
            'forme' => "forme",
            'env' => "env",
            'fonc' => "fonction",
            'gender' => "civilite"
        ];
    
        foreach ($filters as $term => $column) {
            if (isset($terms[$term]) && !empty($terms[$term])) {
                $values = is_array($terms[$term]) ? $terms[$term] : explode(",", $terms[$term]);
                $values = array_map('trim', $values);
    
                if (isset($terms['inclu_' . $term]) && ($terms['inclu_' . $term] === 'false' || $terms['inclu_' . $term] === false || $terms['inclu_' . $term] === 'no' || $terms['inclu_' . $term] === 'non')) {
                    $sql .= " AND $column NOT IN ('" . implode("','", $values) . "')";
                } else {
                    $sql .= " AND $column IN ('" . implode("','", $values) . "')";
                }
            }
        }
    
        // Gestion des filtres complexes
        if (isset($terms['statut']) && !empty($terms['statut'])) {
            $statuts = is_array($terms['statut']) ? $terms['statut'] : explode(",", $terms['statut']);
            $statuts = array_map('trim', $statuts);
            $sql .= " AND (" . implode(" OR ", array_map(function($statut) {
                return "statut LIKE '%$statut%'";
            }, $statuts)) . ")";
        }
    
        if (isset($terms['eff'])) {
            $sql .= " AND effectif IN (" . implode(",", array_map('intval', $terms['eff'])) . ")";
        }
        if (isset($terms['eff_max']) || isset($terms['eff_min'])) {
            if ($terms['eff_var'] === 'inf') {
                $sql .= " AND effectif::integer < " . intval($terms['eff_max']);
            } elseif ($terms['eff_var'] === 'sup') {
                $sql .= " AND effectif::integer > " . intval($terms['eff_min']);
            } elseif ($terms['eff_var'] === 'entre') {
                $sql .= " AND effectif::integer BETWEEN " . intval($terms['eff_min']) . " AND " . intval($terms['eff_max']);
            }
        }
    
        if (isset($terms['ca'])) {
            $sql .= " AND ca IN (" . implode(",", array_map('intval', $terms['ca'])) . ")";
        }
        if (isset($terms['ca_max']) || isset($terms['ca_min'])) {
            if ($terms['ca_var'] === 'inf') {
                $sql .= " AND ca::integer < " . intval($terms['ca_max']);
            } elseif ($terms['ca_var'] === 'sup') {
                $sql .= " AND ca::integer > " . intval($terms['ca_min']);
            } elseif ($terms['ca_var'] === 'entre') {
                $sql .= " AND ca::integer BETWEEN " . intval($terms['ca_min']) . " AND " . intval($terms['ca_max']);
            }
        }
    
        if (isset($terms['limit']) && !empty($terms['limit'])) {
            $sql .= " LIMIT " . intval($terms['limit']);
        }
    
        // Préparation et exécution de la requête
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    
        // Récupération des résultats
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ["result" => $result, "count" => $stmt->rowCount()];
    }
    
    function search2($terms, $user_id) {
    
        if(isset($terms['type'])){
            if(!is_array($terms['type'])){
                $types = explode(",", $terms['type']);
            }else{
                $types = $terms['type'];
            }
            foreach ($types as &$typ) {
                $typ = trim($typ);
            }
            $type = implode(",", $types);
           // echo $type;

            $sql = "SELECT DISTINCT * FROM b2b_france limit 100000 ";
            //-- WHERE emailpro NOT IN (SELECT email FROM blacklist_users WHERE id_user = $user_id )
            //-- AND  blacklist IS NOT TRUE AND (statut IS NULL OR statut='SB')";
            //echo $sql;
        }else{
            echo  json_encode(["message" => "Vous devez spécifié le type de campagne par exemple type=emailpro"]);
            exit;
        }

        //pour la blacklist du client :email NOT IN (SELECT email_contact FROM blacklist WHERE id_client = :idClient)');

        $params = [];
        // Region
        if(isset($terms['region']) && !empty($terms['region'])){
            if(!is_array($terms['region'])){
             $regions = explode(",", $terms['region']);
            }else {
                $regions = $terms['region'];
            }
            $departements= [];

            foreach ($regions as $region) {
                $region = trim($region);
        
                $req = "SELECT code_departement 
                        FROM region_departement_france
                        WHERE nom_region = :region";
        
                $query = $this->db->prepare($req);
                $query->bindParam(':region', $region);
                $query->execute();
        
                // Récupération des résultats de la recherche
                $results = $query->fetchAll(PDO::FETCH_COLUMN, 0);
                $departements = array_merge($departements, $results);
            }
        
            $departements = array_unique($departements);

            // Utilisation des codes départements récupérés
           if (isset($terms['inclu_reg']) && $terms['inclu_reg'] === 'false'||$terms['inclu_reg'] === false || $terms['inclu_reg'] === 'no' || $terms['inclu_reg'] === 'non') {
                $sql .= "AND LEFT(cp, 2) NOT IN ('" . implode("','", $departements) . "')";    
            } else {
                $sql .= "AND LEFT(cp, 2) IN ('" . implode("','", $departements) . "')";    
            }
        }

        // Département
        if (isset($terms['departement']) && !empty($terms['departement'])) {

            if(!is_array($terms['departement'])){
                $departements = explode(",", $terms['departement']);
            }else{
                $departements = $terms['departement'];
            }

            foreach ($departements as &$departement) {
                $departement = trim($departement);
                $req = "SELECT code_departement 
                FROM region_departement_france
                WHERE nom_departement = :departement";

                $query = $this->db->prepare($req);
                $query->bindParam(':departement', $departement);
                $query->execute();

                // Récupération des résultats de la recherche
                $results = $query->fetchAll(PDO::FETCH_COLUMN, 0);
                $departements = array_merge($departements, $results);
            }
                $departements = array_unique($departements);
            
            if (isset($terms['inclu_dep']) && $terms['inclu_dep'] === 'false'||$terms['inclu_dep'] === false || $terms['inclu_dep'] === 'no' || $terms['inclu_dep'] === 'non') {
                $sql .= "AND LEFT(cp, 2) NOT IN ('" . implode("','", $departements) . "')";    
            } else {
                $sql .= "AND LEFT(cp, 2) IN ('" . implode("','", $departements) . "')";    
            }
        }

        // code postaux
        if (($terms['cp']) && !empty($terms['cp'])) {
            if(!is_array($terms['cp'])){
                $codes_postaux = explode(",", $terms['cp']);
            }else{
                $codes_postaux =$terms['cp'];
            }
            foreach ($codes_postaux as &$codes_postal) {
                $codes_postal = trim($codes_postal);
            }
            
            if (isset($terms['inclu_cp'])){
                if($terms['inclu_cp'] === false ||$terms['inclu_cp'] === 'false' || $terms['inclu_cp'] === 'no' || $terms['inclu_cp'] === 'non'){
                $sql .= "AND cp NOT IN ('".implode("','", $codes_postaux)."')";
                }
            }else{
                $sql .= "AND cp IN ('".implode("','", $codes_postaux)."')";
            }
        }

        // Communes
        if (isset($terms['commune']) && !empty($terms['commune'])) {
            if(!is_array($terms['commune'])){
                $communes = explode(",", $terms['commune']);
            }else{
                $communes =$terms['commune'];
            }
            foreach ($communes as &$commune) {
                $commune = trim($commune);
            }
            
            if (isset($terms['inclu_com'])){
                if($terms['inclu_com'] === false ||$terms['inclu_com'] === 'false' || $terms['inclu_com'] === 'no' || $terms['inclu_com'] === 'non'){
                $sql .= "AND communes NOT IN ('".implode("','", $communes)."')";
                }
            }else{
                $sql .= "AND communes IN ('".implode("','", $communes)."')";
            }
        }
     
        // Ville
        if(isset($terms['ville']) && !empty($terms['ville'])){
            if(!is_array($terms['ville'])){
                $villes = explode(",", $terms['ville']);
            }else{
                $villes =$terms['ville'];
            }
            foreach ($villes as &$ville) {
                $ville = trim($ville);
              }
            
            if (isset($terms['inclu_ville']) && $terms['inclu_ville'] === 'false'||$terms['inclu_ville'] === false || $terms['inclu_ville'] === 'no' || $terms['inclu_ville'] === 'non') {
                $sql .= "AND ville NOT IN ('".implode("','", $villes)."')";
            } else {
                $sql .= "AND ville IN ('".implode("','", $villes)."')";
            }
        }

        // Secteur d'activite
        /*if(isset($terms['cat']) && !empty($terms['cat'])){
            if(!is_array($terms['ville'])){
                $cat = explode(",", $terms['cat']);
            }else{
                $cat =$terms['cat'];
            }
            foreach ($cat as &$cat_) {
                $cat_ = trim($cat_);
            }
            if (isset($terms['inclu_cat']) && $terms['inclu_cat'] === 'false' || $terms['inclu_cat'] === false || $terms['inclu_cat'] === 'no' || $terms['inclu_cat'] === 'non') {
                $sql .= "AND cat NOT IN ('".implode("','", $cat)."')";
                
            }else{
                $sql .= "AND cat IN ('".implode("','", $cat)."')";
            }
        }*/
        // catégeorie
        if(isset($terms['categeorie']) && !empty($terms['categeorie'])){
            if(!is_array($terms['categeorie'])){
                $categeories = explode(",", $terms['categeorie']);
            }else {
                $categeories = $terms['categeorie'];
            }
            $codes_categorie = [];
            foreach($categeories as &$categeorie){
                // Récupérer les deux premiers caractères
                $categeorie = substr(trim($categeorie), 0, 2);
                $codes_categorie[] = $categeorie;
            }
            $sql.="AND LEFT(naf, 2) IN ('".implode("','", $codes_categorie)."')";
        }

        // Sous-categeorie
        if(isset($terms['sous_categeorie']) && !empty($terms['sous_categeorie'])){
            if(!is_array($terms['sous_categeorie'])){
                $categeories = explode(",", $terms['sous_categeorie']);
            }else {
                $categeories = $terms['sous_categeorie'];
            }
            $codes_categorie = [];
            foreach($categeories as &$categeorie){
                // Récupérer les deux premiers caractères
                $categeorie = substr(trim($categeorie), 0, 3);
                $codes_categorie[] = $categeorie;
            }
            $sql.="AND LEFT(naf, 3) IN ('".implode("','", $codes_categorie)."')";
        }
        // Naf
        if(isset($terms['naf']) && !empty($terms['naf'])){
            if(!is_array($terms['naf'])){
                $naf = explode(",", $terms['naf']);
            }else{
                $naf =$terms['naf'];
            }
            foreach ($naf as &$naf_) {
                $naf_ = trim($naf_);
              }
            if (isset($terms['inclu_naf']) && $terms['inclu_naf'] === false||$terms['inclu_naf'] === 'false' || $terms['inclu_naf'] === 'no' || $terms['inclu_naf'] === 'non') {
            $sql .= "AND naf NOT IN ('".implode("','", $naf)."')";
            }else
            $sql .= "AND naf IN ('".implode("','", $naf)."')";
        }

        // Effectif
        if(isset($terms['eff']) || !empty($terms['eff'])){
            if (!isset($terms['eff_max']) && !isset($terms['eff_min'])){
                if(!is_array($terms['eff'])){
                    $effectif = explode(",", $terms['eff']);
                }else{
                    $effectif =$terms['eff'];
                }
                foreach ($effectif as &$effectif_) {
                    $effectif_ = trim($effectif_);
                }
                if (isset($terms['inclu_eff']) && $terms['inclu_eff'] === 'false' || $terms['inclu_eff'] === false || $terms['inclu_eff'] === 'no' || $terms['inclu_eff'] === 'non') {
                $sql .= "AND effectif NOT IN ('".implode("','", $effectif)."')";
                }else{
                $sql .= "AND effectif IN ('".implode("','", $effectif)."')";
                }
            }
        }
        if (isset($terms['eff_max']) || isset($terms['eff_min'])){
            if($terms['eff_var'] === 'inf') {
                
                $sql .= "AND (effectif::integer > ".$terms['eff_max'].")";
            }else if($terms['eff_var'] === 'sup') {
                
                    $sql .= "AND (effectif::integer > ".$terms['eff_max'].")";
            }else if($terms['eff_var'] === 'entre'){
                $sql .= "AND effectif::integer >= ".$terms['eff_min']." AND effectif::integer <= ".$terms['eff_valeur_max'];
            }else{
                $sql .= "AND effectif = ".$terms['eff__max'];

            }
                
        }


        // Chiffre d'affaire
        if(isset($terms['ca']) && !empty($terms['ca'])){
            if (!isset($terms['ca_max']) && !isset($terms['ca_min'])){
                if(!is_array($terms['ca'])){
                    $ca = explode(",", $terms['ca']);
                }else{
                    $ca =$terms['ca'];
                }
                foreach ($ca as &$ca_) {
                    $ca_ = trim($ca_);
                }
                if (isset($terms['inclu_ca']) && $terms['inclu_ca'] === 'false' || $terms['inclu_ca'] === false || $terms['inclu_ca'] === 'no' || $terms['inclu_ca'] === 'non') {
                    $sql .= "AND ca NOT IN ('".implode("','", $ca)."')";
                }else{
                $sql .= "AND ca IN ('".implode("','", $ca)."')";
                }
            }   
        } 
        if (isset($terms['ca_max']) || isset($terms['ca_min'])){
            if($terms['ca_var'] === 'inf') {
            
                $sql .= "AND (ca::integer < ".$terms['ca_max'].")";
            }else if($terms['ca_var'] === 'sup') {
                
                    $sql .= "AND (ca::integer > ".$terms['ca_min'].")";
            }else if($terms['ca_var'] === 'entre'){
                $sql .= "AND ca::integer >= ".$terms['ca_min']." AND ca::integer <= ".$terms['ca_max'];
            }else{
                $sql .= "AND ca = ".$terms['ca_valeur_max'];

            }
                
        }

        // Statuts d’établissement
        if(isset($terms['statut']) || !empty($terms['statut'])){
            if(!is_array($terms['statut'])){
                $statuts = explode(",", $terms['statut']);
            }else{
                $statuts =$terms['statut'];
            }
            foreach ($statuts as &$statut) {
                $statut = trim($statut);
            }
            $sql .= " AND statut LIKE '%" . implode("%' OR statut LIKE '%", $statuts) . "%'";
            //statut LIKE '%" . implode("%' OR statut LIKE '%", $statuts) . "%'
          //  $sql .= "AND statut IN ('".implode("','", $statuts)."')";
        }

        // Forme juridique
        if(isset($terms['forme']) && !empty($terms['forme'])){
            if(!is_array($terms['forme'])){
                $forme = explode(",", $terms['forme']);
            }else{
                $forme =$terms['forme'];
            }

            foreach ($forme as &$form) {
                $form = trim($form);
            }
            if (isset($terms['inclu_forme']) && $terms['inclu_forme'] === 'false'|| $terms['inclu_forme'] === false || $terms['inclu_forme'] === 'no' || $terms['inclu_forme'] === 'non') {
                $sql .= "AND forme NOT IN ('".implode("','", $forme)."')";
            }else{
                $sql .= "AND forme IN ('".implode("','", $forme)."')";
            }
        }

        // Environnement
        if(isset($terms['env']) && !empty($terms['env'])){
            if(!is_array($terms['env'])){
                $en = explode(",", $terms['env']);
            }else{
                $en =$terms['env'];
            }
            $sql .= " AND env LIKE '%" . implode("%' OR env LIKE '%", $en) . "%'";
           // $sql .= "AND env IN ('".implode("','", $en)."')";
        }
        // Fonction
        if(isset($terms['fonc']) && !empty($terms['fonc'])){
            if(!is_array($terms['fonc'])){
                $fonc = explode(",", $terms['fonc']);
            }else{
                $fonc =$terms['fonc'];
            }
            $sql .= " AND fonction LIKE '%" . implode("%' OR fonction LIKE '%", $fonc) . "%'";
           // $sql .= "AND fonction IN ('".implode("','", $fonc)."')";
        }
        // Gender
        if(isset($terms['gender']) && !empty($terms['gender'])){
            if(!is_array($terms['gender'])){
                $gend = explode(",", $terms['gender']);
            }else{
                $gend =$terms['gender'];
            }
            $sql .= "AND civilite IN ('".implode("','", $gend)."')";
        }

        if (isset($terms['limit']) && !empty($terms['limit'])){
            $sql .= "LIMIT ".''.$terms['limit'];
        }

        //echo $sql;
        $stmt = $this->db->prepare($sql);

        //echo $params;

        // Exécution de la requête
        //$stmt->execute($params);
        $stmt->execute();

        // Récupération des résultats de la recherche
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $results=["result" =>$result, "count"=>$stmt->rowCount()]; 
       
        //var_dump($result);
    }
 /*  
    function search($terms, $user_id) {
        // Validate required parameter
        if (!isset($terms['type'])) {
        echo json_encode(["message" => "Vous devez spécifié le type de campagne par exemple type=emailpro"]);
        exit;
        }
    
        $sql = "SELECT DISTINCT * FROM b2b 
                WHERE emailpro NOT IN (SELECT email FROM blacklist_users WHERE id_user = $user_id)
                AND blacklist IS NOT TRUE AND (statut IS NULL OR statut='SB')";
    
        // Build filters based on terms
        $filters = [];
        $params = [];
        foreach ($terms as $key => $value) {
        if (in_array($key, ['cp', 'ville', 'cat', 'naf', 'forme', 'ca', 'eff', 'env', 'fonc', 'gend'])) {
            if (!empty($value)) {
            $exploded = explode(",", $value);
            foreach ($exploded as &$item) {
                $item = trim($item);
            }
            $filter_key = $key . (isset($terms["inclu_$key"]) && ($terms["inclu_$key"] === 'false' || $terms["inclu_$key"] === false || $terms["inclu_$key"] === 'no' || $terms["inclu_$key"] === 'non') ? ' NOT IN' : ' IN');
            $filters[] = "$filter_key ('" . implode("','", $exploded) . "')";
            }
        } else if ($key === 'ca_max' || $key === 'ca_min' || $key === 'eff_max' || $key === 'eff_min') {
            if (isset($value) && isset($terms['ca_var']) && isset($terms['limit'])) {
            $ca_var = $terms['ca_var'];
            $limit_operator = ($ca_var === 'inf' || $ca_var === 'sup') ? '>' : 'BETWEEN';
            $filters[] = "$key " . ($limit_operator === 'BETWEEN' ? $limit_operator . ' ' . $terms['ca_min'] . ' AND ' . $terms['ca_valeur_max'] : $limit_operator . ' ' . $value);
            }
        }
        }
    
        if (!empty($filters)) {
        $sql .= ' AND ' . implode(' AND ', $filters);
        }
    
        // Add limit if provided
        if (isset($terms['limit']) && !empty($terms['limit'])) {
        $sql .= " LIMIT " . $terms['limit'];
        }
    
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ["result" => $result, "count" => $stmt->rowCount()];
    }
  */      
}
