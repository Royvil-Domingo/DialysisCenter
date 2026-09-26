<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotFaq extends Model
{
    use HasFactory;

    protected $fillable = ['question', 'answer', 'keywords', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
