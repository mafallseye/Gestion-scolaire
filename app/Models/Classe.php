<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    // Ajoute ces lignes pour autoriser l'enregistrement de ces colonnes
    protected $fillable = [
        'nom_classe',
        'niveau'
    ];

    // Optionnel : Ajoute la relation pour ton diagramme (Une classe a plusieurs étudiants)
    public function students()
    {
        return $this->hasMany(Student::class, 'classe_id');
    }

      public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }
}
