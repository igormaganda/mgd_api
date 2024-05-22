<?php

require_once __DIR__ . '/../models/Product.php';

class ProductController {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }


public function getAllProducts() {
    // Requête SQL pour récupérer tous les produits (fin)
    $stmt = $this->db->query($sql);
    $products = [];
    while ($row = $stmt->fetch()) {
        $products[] = new Product($row['id'], $row['name'], $row['price']);
    }
    return $products;
}

// ... (autres méthodes pour gérer les produits)
}
?>