<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grade;

class GradeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'semestre' => 'required|string',
            'note1' => 'nullable|numeric|min:0|max:20',
            'note2' => 'nullable|numeric|min:0|max:20',
            'note_composition' => 'nullable|numeric|min:0|max:20',
        ]);

        Grade::create($validated);

        return back()->with('message', 'Note ajoutée avec succès !');
    }

    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'note1' => 'nullable|numeric|min:0|max:20',
            'note2' => 'nullable|numeric|min:0|max:20',
            'note_composition' => 'nullable|numeric|min:0|max:20',
            'semestre' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $grade->update($validated);

        return back()->with('message', 'Note mise à jour avec succès');
    }
}
