<?php

namespace App\AmoCRM\Interfaces;

interface LeadServiceInterface
{
    public function create(array $leadData): int;
}