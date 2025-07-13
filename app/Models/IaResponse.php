<?php
// app/Models/IaResponse.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IaResponse extends Model
{
    use HasFactory;

    protected $fillable = ['question_id', 'model_name', 'response', 'token_usage', 'response_time'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
