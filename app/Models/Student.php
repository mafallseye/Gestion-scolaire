<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['matricule', 'nom', 'prenom', 'classe_id', 'user_id'];

    // On indique à Laravel que ces attributs (calculés dans le controller ou ici)
    // doivent être inclus quand on envoie l'étudiant vers Vue.js
    // protected $appends = ['average', 'avg_s1', 'avg_s2'];
    // protected $appends = ['average', 'credits', 'status'];

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    /**
     * RELATION : Un étudiant peut être lié à un compte utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }


}
