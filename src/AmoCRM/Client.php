<?php

namespace App\AmoCRM;

use App\AmoCRM\Interfaces\ClientInterface;

class Client implements ClientInterface
{
    private const API_VERSION = 'v4';
    private const TIMEOUT = 10;

    public function __construct(
        private string $baseDomain,
        private string $clientId,
        private string $accessToken
    ) {
        $this->baseDomain = rtrim($baseDomain, '/');
    }

    public function createLead(array $leadData): array
    {
        return $this->post('leads', [$leadData]);
    }

    public function createContact(array $contactData): array
    {
        return $this->post('contacts', [$contactData]);
    }

    public function linkContactToLead(int $contactId, int $leadId): void
    {
        $this->post("leads/$leadId/link", [[
            'to_entity_id' => $contactId,
            'to_entity_type' => 'contacts'
        ]]);
    }

    private function post(string $endpoint, array $data): array
    {
        $url = "{$this->baseDomain}/api/" . self::API_VERSION . "/{$endpoint}";
        
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'X-Client-Id: ' . $this->clientId,
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => json_encode($data),
                'ignore_errors' => true,
                'timeout' => self::TIMEOUT,
            ]
        ]);

        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new \RuntimeException('Запрос провален');
        }
        
        return json_decode($response, true) ?: [];
    }
}