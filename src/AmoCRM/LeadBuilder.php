<?php

namespace App\AmoCRM;

use App\AmoCRM\Abstracts\AbstractBuilder;

class LeadBuilder extends AbstractBuilder
{
    public function __construct()
    {
        $this->data = [
            'name' => 'Новая заявка',
            'price' => 0,
        ];
    }

    public function setPrice(float $price): self
    {
        $this->data['price'] = $price;
        return $this;
    }

    public function setTimeSpentFlag(bool $value, int $fieldId): self
    {
        $this->data['custom_fields_values'] = [
            [
                'field_id' => $fieldId,
                'values' => [['value' => $value]]
            ]
        ];
        return $this;
    }
}