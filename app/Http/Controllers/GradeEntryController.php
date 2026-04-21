<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Grade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradeEntryController extends Controller
{
    public function index(Request $request)
{
    $classeId = $request->input('classe_id');
    $subjectId = $request->input('subject_id');
    // On définit "Semestre 1" par défaut si rien n'est sélectionné
    $semestre = $request->input('semestre', 'Semestre 1');

    return Inertia::render('Grades/MassEntry', [
        'classes' => Classe::all(),
        'subjects' => Subject::all(),

        'students' => $classeId ? Student::where('classe_id', $classeId)
            ->with(['grades' => function($q) use ($subjectId, $semestre) {
                // On utilise les variables nettoyées
                $q->where('subject_id', $subjectId)
                  ->where('semestre', $semestre);
            }])->get() : [],

        // On renvoie les filtres pour que Vue sache quel semestre est actif
        'filters' => [
            'classe_id' => $classeId,
            'subject_id' => $subjectId,
            'semestre' => $semestre,
        ]
    ]);
}


  public function store(Request $request)
{
    $data = $request->validate([
        'subject_id' => 'required|exists:subjects,id',
        'semestre' => 'required|string',
        'notes' => 'required|array',
    ]);

  foreach ($request->notes as $studentId => $data) {
    // Vérifie qu'au moins une note est présente
    if (isset($data['note1']) || isset($data['note2']) || isset($data['note_composition'])) {
        Grade::updateOrCreate(
            [
                'student_id' => $studentId,
                'subject_id' => $request->subject_id,
                'semestre'   => $request->semestre,
            ],
            [
                'note1'            => $data['note1'] ?? null,
                'note2'            => $data['note2'] ?? null,
                'note_composition' => $data['note_composition'] ?? null, // <--- Vérifie bien ce nom
            ]
        );
    }
}


    return back()->with('message', 'Toutes les notes ont été synchronisées !');
}

}
