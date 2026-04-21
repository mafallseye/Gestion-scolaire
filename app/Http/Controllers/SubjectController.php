<?php

namespace App\Http\Controllers;

use App\Models\Subject; // Importation indispensable
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Pour générer le slug automatiquement

class SubjectController extends Controller
{
  public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'ue_code' => 'required|string',
        'ue_nom' => 'required|string',
        'credits' => 'required|integer|min:1',
        'coefficient' => 'nullable|integer|min:1' // On accepte le coef envoyé
    ]);

    // On s'assure que si le coef est vide, on met 1, sinon on garde la valeur saisie
    $validated['coefficient'] = $request->coefficient ?? 1;
    $validated['slug'] = \Illuminate\Support\Str::slug($request->name);

    Subject::create($validated);

    return back()->with('message', 'Matière créée !');
}


 public function update(Request $request, Subject $subject)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'ue_code' => 'required|string',
        'ue_nom' => 'required|string',
        'credits' => 'required|integer|min:1',
        'coefficient' => 'required|integer|min:1',
    ]);

    $subject->update($validated);

    return back()->with('message', 'Matière mise à jour avec succès !');
}



}
