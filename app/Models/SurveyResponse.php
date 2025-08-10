<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'response',
        'ip_address',
        'user_agent',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
}
