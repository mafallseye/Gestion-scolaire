<?php

namespace App\Http\Controllers;

use App\Models\Timetable;
use App\Models\Classe;
use App\Models\Subject;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimetableController extends Controller
{
    // Afficher la page de l'emploi du temps
 public function index(Request $request)
{
    $user = auth()->user();
    $classeId = $request->input('classe_id');

    // SI C'EST UN ÉTUDIANT : On force sa propre classe_id
    if ($user->role === 'student') {
        $student = \App\Models\Student::where('user_id', $user->id)->first();
        $classeId = $student ? $student->classe_id : null;
    }

    return Inertia::render('Academic/Timetable', [
        'classes' => \App\Models\Classe::all(),
        'subjects' => \App\Models\Subject::all(),
        'schedule' => $classeId ? \App\Models\Timetable::where('classe_id', $classeId)
            ->with('subject')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day') : [],
        'filters' => ['classe_id' => $classeId]
    ]);
}


    // Enregistrer un nouveau cours
    public function store(Request $request)
    {
        $validated = $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'day' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
            'room' => 'nullable|string',
        ]);

        Timetable::create($validated);

        return back()->with('message', 'Cours ajouté avec succès !');
    }

    // Supprimer un cours
    public function destroy(Timetable $timetable)
    {
        $timetable->delete();
        return back()->with('message', 'Cours supprimé !');
    }

    public function downloadPdf(Classe $classe)
{
    $schedule = Timetable::where('classe_id', $classe->id)
                ->with('subject')
                ->orderBy('start_time')
                ->get()
                ->groupBy('day');

    $pdf = Pdf::loadView('pdf.timetable', [
        'classe' => $classe,
        'schedule' => $schedule,
        'days' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
    ]);

    return $pdf->download("Emploi_du_temps_{$classe->nom_classe}.pdf");
}

}
