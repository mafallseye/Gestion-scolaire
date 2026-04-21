<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    // Indique les champs que l'on peut remplir (Mass Assignment)
    protected $fillable = ['title', 'file_path', 'subject_id'];

    // Relation inverse : Une leçon appartient à une matière
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
