<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Classe;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    // C'EST CETTE FONCTION QUI MANQUAIT :
    public function index()
    {
        return Inertia::render('Admin/Lessons/Index', [
             'lessons' => Lesson::with('subject')->latest()->get(),
        // IMPORTANT : On charge les subjects liés à chaque classe
        'classes' => Classe::with('subjects')->get(),
        // Optionnel : toutes les matières si tu veux un sélecteur simple
        'subjects' => Subject::orderBy('name')->get(),
        'classes' => \App\Models\Classe::with('subjects')->get(),
        ]);
    }

    // Ta fonction store que nous avons vue ensemble
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'file' => 'required|mimes:pdf|max:10000',
        ]);

        $path = $request->file('file')->store('lessons', 'public');

        Lesson::create([
            'title' => $request->title,
            'subject_id' => $request->subject_id,
            'file_path' => $path,
        ]);

        return back()->with('success', 'Le cours a été publié.');
    }
}
