<?php

namespace App\Command;

use App\Entity\Post;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GetPostsCommand extends Command {

    protected static $defaultName = 'app:get-posts';
    private const API_URL = "https://jsonplaceholder.typicode.com/";

    private EntityManagerInterface $entityManager;

    /**
     * GetPostsCommand constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = HttpClient::create();
        try {
            $response = $client->request('GET', self::API_URL . "posts");

            if ($response->getStatusCode() === 200) {
                $posts = $response->toArray();
                // Store user names in array to prevent unnecessary API calls
                $users = [];

                foreach ($posts as $post) {
                    // Check if post already exists
                    if (is_null($this->entityManager->getRepository(Post::class)->find($post['id']))) {
                        $postObject = new Post();

                        // Get user name from API if it doesn't exist in local array
                        if (!in_array($post['userId'], $users)) {
                            $name = $this->getUserName($post['userId']);
                            if (is_null($name)) {
                                $output->writeln('Wystąpił problem podczas pobierania danych z API.');
                                return 0;
                            }
                            $users[$post['userId']] = $name;
                        }

                        // Store post in db
                        $postObject
                            ->setId($post['id'])
                            ->setBody($post['body'])
                            ->setTitle($post['title'])
                            ->setAuthor($users[$post['userId']]);

                        $this->entityManager->persist($postObject);
                    }
                }
            } else {
                $output->writeln('Wystąpił problem przy próbie połączenia z API.');
            }
        } catch (TransportExceptionInterface
            |ClientExceptionInterface
            |DecodingExceptionInterface
            |RedirectionExceptionInterface
            |ServerExceptionInterface $e) {
            $output->writeln("Wystąpił błąd: {$e->getMessage()}");
        }

        $this->entityManager->flush();

        $output->writeln('Pobieranie danych zakończone pomyślnie');

        return 0;
    }

    /**
     * Fetch user name from API
     *
     * @param int $id
     * @return string|null
     */
    private function getUserName(int $id): ?string {
        $client = HttpClient::create();
        try {
            $response = $client->request('GET', self::API_URL . "users/$id");
            if ($response->getStatusCode() === 200) {
                $user = $response->toArray();
                return $user['name'];
            } else {
                return null;
            }
        } catch (TransportExceptionInterface
            |ClientExceptionInterface
            |DecodingExceptionInterface
            |RedirectionExceptionInterface
            |ServerExceptionInterface $e) {
            return null;
        }
    }

}