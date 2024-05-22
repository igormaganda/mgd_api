<?php
use PHPUnit\Framework\TestCase;

// Inclure les classes à tester
require_once 'path/to/Controller/UserController.php';

class UserControllerTest extends TestCase
{
    // Test de la fonction register
    public function testRegister()
    {
        $userController = new UserController();

        // Appel de la méthode avec des paramètres valides
        $result = $userController->register('testuser', 'testpassword');

        // Vérification du résultat
        $this->assertArrayHasKey('token', $result);
    }

    // Test de la fonction getUserByUsername
    public function testGetUserByUsername()
    {
        $userController = new UserController();

        // Appel de la méthode avec un nom d'utilisateur existant
        $result = $userController->getUserByUsername('existinguser');

        // Vérification du résultat
        $this->assertNotNull($result);

        // Appel de la méthode avec un nom d'utilisateur inexistant
        $result = $userController->getUserByUsername('nonexistentuser');

        // Vérification du résultat
        $this->assertNull($result);
    }
}
