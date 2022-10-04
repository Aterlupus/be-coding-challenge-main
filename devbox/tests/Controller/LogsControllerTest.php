<?php
declare(strict_types=1);

namespace Tests\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\Fixtures\EntityGenerator;
use Tests\Fixtures\LogsFixtures;

class LogsControllerTest extends WebTestCase
{
    const COUNT_ENDPOINT = '/count';

    private KernelBrowser $client;

    private EntityManagerInterface $entityManager;

    private EntityGenerator $entityGenerator;


    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $this->entityGenerator = new EntityGenerator($this->entityManager);
        $this->entityManager->beginTransaction();
    }

    public function testItGetsCount()
    {
        $fixtures = new LogsFixtures(new EntityGenerator($this->entityManager));
        $fixtures->load($this->entityManager);

        $this->client->request('GET', self::COUNT_ENDPOINT);
        $this->assertResponseIsSuccessful();

        $responseJson = json_decode($this->client->getResponse()->getContent(), true);

        self::assertCount(1, $responseJson);
        self::assertArrayHasKey('counter', $responseJson);
        self::assertEquals(10, $responseJson['counter']);
    }

    //TODO: Abstract
    public function testItFiltersByServiceName()
    {
        $logsImport = $this->entityGenerator->getLogsImport();
        $logsEntry = $this->entityGenerator->getLogsEntry($logsImport);
        $logsEntry->setServiceName('abc');
        $this->entityManager->flush();

        $this->client->request('GET', self::COUNT_ENDPOINT);
        $this->assertResponseIsSuccessful();
        $responseJson = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(1, $responseJson['counter']);

        $this->client->request('GET', self::COUNT_ENDPOINT, ['serviceNames' => ['def']]);
        $this->assertResponseIsSuccessful();
        $responseJson = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(0, $responseJson['counter']);
    }

    public function testItFiltersByStartDate()
    {
        $logsImport = $this->entityGenerator->getLogsImport();
        $logsEntry = $this->entityGenerator->getLogsEntry($logsImport);
        $logsEntry->setDateTime(new DateTime('2022-01-01'));
        $this->entityManager->flush();

        $this->client->request('GET', self::COUNT_ENDPOINT);
        $this->assertResponseIsSuccessful();
        $responseJson = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(1, $responseJson['counter']);

        $this->client->request('GET', self::COUNT_ENDPOINT, ['startDate' => '2023-01-01']);
        $this->assertResponseIsSuccessful();
        $responseJson = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(0, $responseJson['counter']);
    }

    public function testItFiltersByEndDate()
    {
        $logsImport = $this->entityGenerator->getLogsImport();
        $logsEntry = $this->entityGenerator->getLogsEntry($logsImport);
        $logsEntry->setDateTime(new DateTime('2022-01-01'));
        $this->entityManager->flush();

        $this->client->request('GET', self::COUNT_ENDPOINT);
        $this->assertResponseIsSuccessful();
        $responseJson = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(1, $responseJson['counter']);

        $this->client->request('GET', self::COUNT_ENDPOINT, ['endDate' => '2021-01-01']);
        $this->assertResponseIsSuccessful();
        $responseJson = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(0, $responseJson['counter']);
    }

    public function testItFiltersByStatusCode()
    {
        $logsImport = $this->entityGenerator->getLogsImport();
        $logsEntry = $this->entityGenerator->getLogsEntry($logsImport);
        $logsEntry->setStatusCode(200);
        $this->entityManager->flush();

        $this->client->request('GET', self::COUNT_ENDPOINT);
        $this->assertResponseIsSuccessful();
        $responseJson = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(1, $responseJson['counter']);

        $this->client->request('GET', self::COUNT_ENDPOINT, ['statusCode' => 404]);
        $this->assertResponseIsSuccessful();
        $responseJson = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(0, $responseJson['counter']);
    }

    public function testItFailsOnNonArrayServiceNames()
    {
        $this->client->request('GET', self::COUNT_ENDPOINT, ['serviceNames' => 'abc']);

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testItAcceptsDateAsStartDate()
    {
        $this->client->request('GET', self::COUNT_ENDPOINT, ['startDate' => '2022-01-01']);

        self::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testItFailsGracefullyOnArrayParsedAsStartDate()
    {
        $this->client->request('GET', self::COUNT_ENDPOINT, ['startDate' => ['x', 'y']]);

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testItAcceptsDateTimeAsStartDate()
    {
        $this->client->request('GET', self::COUNT_ENDPOINT, ['startDate' => '2022-01-01T12:34:56+00:00']);

        self::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testItFailsOnInvalidStartDateFormat()
    {
        $this->client->request('GET', self::COUNT_ENDPOINT, ['startDate' => 'xxx']);

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testItFailsOnInvalidEndDateFormat()
    {
        $this->client->request('GET', self::COUNT_ENDPOINT, ['endDate' => 'xxx']);

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }
}
