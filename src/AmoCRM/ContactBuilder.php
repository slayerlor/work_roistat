<?php

namespace App\AmoCRM;

use App\AmoCRM\Abstracts\AbstractBuilder;

class ContactBuilder extends AbstractBuilder
{
    public function __construct()
    {
        $this->data = [
            'name' => '',
            'custom_fields_values' => []
        ];
    }

    public function setName(string $name): self
    {
        $this->data['name'] = $name;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->data['custom_fields_values'][] = [
            'field_code' => 'EMAIL',
            'values' => [['value' => $email]]
        ];
        return $this;
    }

    public function setPhone(string $phone): self
    {
        $this->data['custom_fields_values'][] = [
            'field_code' => 'PHONE',
            'values' => [['value' => $phone]]
        ];
        return $this;
    }
}