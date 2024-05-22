<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class IntegrationTest extends TestCase
{
    protected $client;

    protected function setUp(): void
    {
        // Initialisation du client HTTP pour les tests d'intégration
        $this->client = new Client([
            'base_uri' => 'http://localhost/api/', // URL de votre API
            'http_errors' => false // Désactiver les erreurs HTTP pour les tests
        ]);
    }

    // Test d'une requête POST au contrôleur UserController pour l'inscription
    public function testRegisterEndpoint()
    {
        $response = $this->client->post('register', [
            'form_params' => [
                'login' => 'testuser',
                'password' => 'testpassword'
            ]
        ]);

        // Vérification du code de statut HTTP
        $this->assertEquals(200, $response->getStatusCode());

        // Vérification du contenu de la réponse (peut varier selon votre implémentation)
        $responseData = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('token', $responseData);
    }

    // Autres tests d'intégration similaires pour tester d'autres endpoints de votre API
}
