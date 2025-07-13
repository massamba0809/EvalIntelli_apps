<?php

// app/Models/Domain.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'prompt_template'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
