<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['created_by', 'first_name', 'last_name', 'email', 'mobile_number', 'organization', 'designation', 'department', 'secretary_name', 'secretary_email', 'email_status', 'extra'])]
class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return ['extra' => 'array'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(ContactGroup::class)->withTimestamps();
    }
}
