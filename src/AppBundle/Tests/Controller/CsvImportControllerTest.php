<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CsvImportControllerTest extends WebTestCase
{
    public function testImportcsv()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/importCsv');
    }

}
