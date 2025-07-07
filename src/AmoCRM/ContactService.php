<?php

namespace App\AmoCRM;

use App\AmoCRM\Interfaces\ClientInterface;
use App\AmoCRM\Interfaces\ContactServiceInterface;

class ContactService implements ContactServiceInterface
{
    public function __construct(
        private readonly ClientInterface $client
    ) {}

    public function create(array $contactData): int
    {
        $response = $this->client->createContact($contactData);
        
        if (empty($response['_embedded']['contacts'][0]['id'])) {
            throw new \RuntimeException('Создание контакта провалено');
        }
        
        return (int)$response['_embedded']['contacts'][0]['id'];
    }

    public function linkToLead(int $contactId, int $leadId): void
    {
        $this->client->linkContactToLead($contactId, $leadId);
    }
}