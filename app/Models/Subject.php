<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany; // Optionnel mais propre

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
   // app/Models/Subject.php
protected $fillable = ['name', 'slug', 'ue_code', 'ue_nom', 'credits', 'coefficient'];


public function lessons() {
    return $this->hasMany(Lesson::class);
}
public function classes(): BelongsToMany
{
    return $this->belongsToMany(Classe::class);
}


}

