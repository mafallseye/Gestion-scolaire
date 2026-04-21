<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Classe;   // <--- VÉRIFIE CETTE LIGNE


use App\Models\Subject;
class ClassroomController extends Controller
{
public function index()
{
    return Inertia::render('Academic/Index', [
        'classes' => \App\Models\Classe::withCount('students')->get(),
        'subjects' => \App\Models\Subject::all(),
    ]);
}

public function storeClass(Request $request)
{
    $validated = $request->validate([
        'nom_classe' => 'required|string|max:255',
        'niveau' => 'required|string',
        'subject_ids' => 'nullable|array',
        'subject_ids.*' => 'exists:subjects,id',
    ]);

    // 1. ON ENREGISTRE LA CLASSE DANS UNE VARIABLE $classe
    $classe = \App\Models\Classe::create([
        'nom_classe' => $validated['nom_classe'],
        'niveau' => $validated['niveau']
    ]);

    // 2. L'ACTION MAGIQUE : $classe existe maintenant et on peut lier les matières
    if ($request->has('subject_ids')) {
        $classe->subjects()->attach($request->subject_ids);
    }

    return back()->with('success', 'Classe et programme créés avec succès !');
}

public function destroyClass(Classe $classe)
{
    $classe->delete();
    return back()->with('message', 'Classe supprimée');
}

public function destroySubject(Subject $subject)
{
    $subject->delete();
    return back()->with('message', 'Matière supprimée');
}
public function updateClassSubjects(Request $request, Classe $classe)
{
    $request->validate([
        'subject_ids' => 'required|array',
        'subject_ids.*' => 'exists:subjects,id',
    ]);

    // La méthode sync() met à jour la table pivot automatiquement
    $classe->subjects()->sync($request->subject_ids);

    return back()->with('success', 'Programme de la classe mis à jour !');
}


}
