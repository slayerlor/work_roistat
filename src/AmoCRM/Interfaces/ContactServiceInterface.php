<?php

namespace App\AmoCRM\Interfaces;

interface ContactServiceInterface
{
    public function create(array $contactData): int;
    public function linkToLead(int $contactId, int $leadId): void;
}