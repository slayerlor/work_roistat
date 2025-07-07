<?php

namespace App\AmoCRM\Interfaces;

interface ClientInterface
{
    public function createLead(array $leadData): array;
    public function createContact(array $contactData): array;
    public function linkContactToLead(int $contactId, int $leadId): void;
}