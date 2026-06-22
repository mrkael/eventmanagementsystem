<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['event_id', 'created_by', 'type', 'file_path', 'status', 'total_rows', 'processed_rows', 'summary'])]
class Import extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'summary' => 'array',
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
        ];
    }
}
