<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    protected $fillable = ['classe_id', 'subject_id', 'day', 'start_time', 'end_time', 'room'];

    // Relation : Un cours appartient à une matière
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // Relation : Un cours appartient à une classe
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }
}
