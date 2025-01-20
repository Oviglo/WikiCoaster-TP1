<?php

namespace App\Tests\Controller;

use App\Repository\ParkRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * @coversNothing
 */
class ParkControllerTest extends WebTestCase
{
    public function testDelete()
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $parkRepository = static::getContainer()->get(ParkRepository::class);

        $park = $parkRepository->findOneBy(['name' => 'Disneyland Paris']);
        $client->loginUser($userRepository->findOneBy(['username' => 'admin']));

        $client->request('GET', '/park/'.$park->getId());
        $client->submitForm('Delete');

        $this->assertResponseRedirects('/park', Response::HTTP_SEE_OTHER);
    }
}
