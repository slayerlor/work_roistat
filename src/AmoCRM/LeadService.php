<?php

namespace App\AmoCRM;

use App\AmoCRM\Interfaces\ClientInterface;
use App\AmoCRM\Interfaces\LeadServiceInterface;

class LeadService implements LeadServiceInterface
{
    public function __construct(
        private readonly ClientInterface $client
    ) {}

    public function create(array $leadData): int
    {
        $response = $this->client->createLead($leadData);
        
        if (empty($response['_embedded']['leads'][0]['id'])) {
            throw new \RuntimeException('Создание лида провалено');
        }
        
        return (int)$response['_embedded']['leads'][0]['id'];
    }
}