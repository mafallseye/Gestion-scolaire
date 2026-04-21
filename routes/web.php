<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\StudentController;

use App\Http\Controllers\GradeController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ClassroomController;

use App\Http\Controllers\GradeEntryController;
use App\Http\Controllers\TimetableController;
Route::get('/students/{student}/bulletin-annuel', [StudentController::class, 'downloadAnnualBulletin'])
     ->name('students.annual-bulletin');


// Route pour l'Administrateur : Déposer un cours
use App\Http\Controllers\LessonController;
Route::get('/my-grades', [App\Http\Controllers\StudentController::class, 'myGrades'])
    ->name('student.grades')
    ->middleware(['auth']);

// Remplace ton ancienne route subject par celle-ci
Route::put('/subjects/{subject}', [App\Http\Controllers\SubjectController::class, 'update'])->name('subjects.update');

// OU si tu veux être tranquille, utilise match pour accepter les deux :
Route::match(['put', 'patch'], '/subjects/{subject}', [App\Http\Controllers\SubjectController::class, 'update'])->name('subjects.update');



Route::post('/classes/{classe}/subjects', [ClassroomController::class, 'updateClassSubjects'])->name('classes.update-subjects');


Route::get('/admin/lessons', [LessonController::class, 'index'])
    ->name('admin.lessons.index')
    ->middleware(['auth']);


Route::post('/lessons/store', [LessonController::class, 'store'])->name('lessons.store')->middleware('auth');

Route::post('/admin/lessons', [App\Http\Controllers\LessonController::class, 'store'])
    ->name('admin.lessons.store')
    ->middleware(['auth', 'role:admin']); // Assure-toi que ton middleware 'role' existe

// Route pour l'Étudiant : Télécharger (facultatif si tu utilises storage:link)
Route::get('/lessons/{lesson}/download', [App\Http\Controllers\LessonController::class, 'download'])
    ->name('lessons.download')
    ->middleware(['auth']);

Route::get('/timetable/download/{classe}', [TimetableController::class, 'downloadPdf'])->name('timetable.download');


Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');
Route::post('/timetable', [TimetableController::class, 'store'])->name('timetable.store');
Route::delete('/timetable/{timetable}', [TimetableController::class, 'destroy'])->name('timetable.destroy');


Route::get('/grades/mass-entry', [GradeEntryController::class, 'index'])->name('grades.mass-entry');
Route::post('/grades/mass-entry', [GradeEntryController::class, 'store'])->name('grades.mass-store');

Route::delete('/classes/{classe}', [ClassroomController::class, 'destroyClass'])->name('classes.destroy');
Route::delete('/subjects/{subject}', [ClassroomController::class, 'destroySubject'])->name('subjects.destroy');

Route::post('/classes', [App\Http\Controllers\ClassroomController::class, 'storeClass'])->name('classes.store');


Route::get('/academic', [ClassroomController::class, 'index'])->name('academic.index');
Route::post('/classes', [ClassroomController::class, 'storeClass'])->name('classes.store');

Route::patch('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');

Route::patch('/grades/{grade}', [GradeController::class, 'update'])->name('grades.update');


Route::post('/subjects', [SubjectController::class, 'store'])->middleware(['auth'])->name('subjects.store');


Route::patch('/students/{student}/link-user', [StudentController::class, 'linkUser'])->name('students.linkUser');

Route::middleware(['auth', 'verified'])->group(function () {
    // Page de gestion des utilisateurs
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    // Action de modification du rôle
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
});

Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
Route::get('/students/{student}/bulletin', [StudentController::class, 'downloadBulletin'])->name('students.bulletin');
Route::patch('/students/{student}', [StudentController::class, 'update'])->name('students.update');


Route::post('/grades', [GradeController::class, 'store'])->middleware(['auth'])->name('grades.store');

Route::get('/dashboard', [StudentController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Route pour enregistrer (POST)
Route::post('/students', [StudentController::class, 'store'])->name('students.store');



Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
