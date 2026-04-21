<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
// protected $fillable = ['student_id', 'subject_id', 'semestre', 'note1', 'note2', 'note_composition','note_rattrapage'];
protected $appends = ['moyenne_module'];
// app/Models/Grade.php

protected $fillable = [
    'student_id',
    'subject_id',
    'semestre',
    'note1',
    'note2',
    'note_composition',
    'note_rattrapage',
    'moyenne_module' // <--- AJOUTE ÇA ICI
];

// Tu peux garder $appends si tu as un accesseur,
// mais pour le LMD, il vaut mieux avoir la valeur réelle en base.



// Dans app/Models/Grade.php
public function subject()
{
    return $this->belongsTo(Subject::class, 'subject_id');
}



// Ajoute cette fonction pour calculer la moyenne du module
// public function getMoyenneModuleAttribute()
// {
//     $notesCC = collect([$this->note1, $this->note2])->filter(fn($n) => !is_null($n));
//     if ($notesCC->isEmpty() && is_null($this->note_composition)) return 0;

//     $moyenneCC = $notesCC->count() > 0 ? $notesCC->avg() : 0;

//     // Si on a une composition, on fait (Moyenne CC + Composition) / 2
//     if (!is_null($this->note_composition)) {
//         return ($moyenneCC + $this->note_composition) / 2;
//     }

//     return $moyenneCC;
// }

// app/Models/Grade.php



public function getMoyenneModuleAttribute()
{
    $notesCC = collect([$this->note1, $this->note2])->filter(fn($n) => !is_null($n));
    $moyenneCC = $notesCC->count() > 0 ? $notesCC->avg() : 0;

    // Déterminer la note d'examen à utiliser (La meilleure entre Comp et Rattrapage)
    $noteExamen = max($this->note_composition ?? 0, $this->note_rattrapage ?? 0);

    // Formule LMD classique : (Moyenne CC + Examen) / 2
    return ($moyenneCC + $noteExamen) / 2;
}

// Pour savoir si l'élève a utilisé le rattrapage
public function getSessionAttribute()
{
    return (!is_null($this->note_rattrapage) && $this->note_rattrapage > $this->note_composition)
           ? 'Session 2'
           : 'Session 1';
}


}

