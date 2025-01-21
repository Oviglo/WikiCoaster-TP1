<?php

namespace App\Tests\Controller;

use App\Repository\CoasterRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CoasterControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        // Va sur la page de la liste des coasters
        $client->request('GET', '/coaster/');

        $this->assertResponseIsSuccessful(); // Test le code retour 200
    }

    public function testNew()
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $adminUser = $userRepository->findOneBy(['username' => 'admin']);
        $client->loginUser($adminUser);

        $client->request('GET', '/coaster/add');

        $this->assertResponseIsSuccessful();

        // Envoi du formulaire avec des données
        $client->submitForm('Ajouter', [
            'coaster[name]' => 'Coaster test',
            'coaster[maxSpeed]' => 120,
            'coaster[maxHeight]' => 80,
            'coaster[length]' => 1200,
        ]);

        $this->assertResponseRedirects();

        $coasterRepository = static::getContainer()->get(CoasterRepository::class);
        $newCoaster = $coasterRepository->findOneBy(['name' => 'Coaster test']);

        $this->assertEquals('Coaster test', $newCoaster->getName());
    }
}