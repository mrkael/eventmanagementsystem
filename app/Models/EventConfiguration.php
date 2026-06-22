<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'is_default', 'registration_rules', 'qr_rules', 'capacity_rules', 'email_settings'])]
class EventConfiguration extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'registration_rules' => 'array',
            'qr_rules' => 'array',
            'capacity_rules' => 'array',
            'email_settings' => 'array',
        ];
    }
}
