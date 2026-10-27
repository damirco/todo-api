<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'priority',
        'category_id',
        'status',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'priority' => TaskPriority::class,
        'status' => TaskStatus::class,
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
