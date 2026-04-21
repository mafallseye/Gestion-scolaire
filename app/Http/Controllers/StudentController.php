<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia; // Importation correcte
use Illuminate\Support\Facades\Redirect;
use Barryvdh\DomPDF\Facade\Pdf; // N'oublie pas l'import en haut !

class StudentController extends Controller
{
    // 1. Affichage de la page avec les données
public function index(Request $request)
{
    $user = auth()->user();
    $search = $request->input('search');
    $sort = $request->input('sort');

    // --- CAS 1 : ÉTUDIANT ---
      // --- CAS : L'UTILISATEUR EST UN ÉTUDIANT ---
   // --- CAS : L'UTILISATEUR EST UN ÉTUDIANT ---
 if ($user->role === 'student') {
        // On initialise la variable en la cherchant en base
        $student = Student::where('user_id', $user->id)
            ->with(['grades.subject', 'classe', 'classe.subjects.lessons'])
            ->first();

              // AJOUTE CETTE VÉRIFICATION ICI
    if (!$student) {
        return Inertia::render('StudentSpace', [
            'student' => null,
            'error' => "Votre profil étudiant n'a pas encore été créé."
        ]);
    }

     // 1. CALCULS PAR SEMESTRE
$gradesS1 = $student->grades->where('semestre', 'Semestre 1');
$gradesS2 = $student->grades->where('semestre', 'Semestre 2');

// On utilise ta fonction LMD pour chaque semestre afin d'avoir les crédits compensés
$lmdS1 = $this->calculateStudentLMD((object)['grades' => $gradesS1]);
$lmdS2 = $this->calculateStudentLMD((object)['grades' => $gradesS2]);

// On injecte les moyennes ET les crédits
$student->detail_s1 = [
    'avg' => $lmdS1['average'] ?: '0.00',
    'credits' => $lmdS1['credits']
];
$student->detail_s2 = [
    'avg' => $lmdS2['average'] ?: '0.00',
    'credits' => $lmdS2['credits']
];

// 2. CALCUL ANNUEL GLOBAL
$lmd = $this->calculateStudentLMD($student);
$student->average = $lmd['average'];
$student->credits = $lmd['credits'];

// ... reste de ta logique de décision (Admis, Passage Cond, etc.) ...


    // Décision (Admis si moyenne annuelle >= 10)
    if (floatval($student->average) >= 10) {
        $student->status = 'Admis';
        $student->credits = 60;
    } elseif (intval($student->credits) >= 42) {
        $student->status = 'Passage Conditionnel';
    } else {
        $student->status = (floatval($student->average) > 0) ? 'Ajourné' : 'Incomplet';
    }

        // On envoie $student à la vue (qu'il soit trouvé ou null)
        return Inertia::render('StudentSpace', [
            'student' => $student
        ]);
    }




    // --- CAS 2 : ADMIN / ENSEIGNANT ---
    $availableUsers = \App\Models\User::whereDoesntHave('student')->get();

    $query = Student::query()->with(['grades.subject', 'classe']);

    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('nom', 'like', "%{$search}%")
              ->orWhere('prenom', 'like', "%{$search}%")
              ->orWhere('matricule', 'like', "%{$search}%");
        });
    }

    // $liste = $query->get()->map(function ($student) {
    //     // On calcule la moyenne générale (Annuelle)
    //     $student->average = $this->calculateGlobalAverage($student);

    //     // Calculs par semestre
    //     $student->avg_s1 = $this->calculateSemestreAvg($student, 'Semestre 1');
    //     $student->avg_s2 = $this->calculateSemestreAvg($student, 'Semestre 2');

    //     return $student;
$liste = $query->get()->map(function ($student) {
    // A. On isole les notes SANS toucher à la base de données
    $gradesS1 = $student->grades->where('semestre', 'Semestre 1')->values();
    $gradesS2 = $student->grades->where('semestre', 'Semestre 2')->values();

    // B. On calcule chaque partie INDÉPENDAMMENT
    $lmdS1 = $this->calculateStudentLMD($gradesS1);
    $lmdS2 = $this->calculateStudentLMD($gradesS2);
    $lmdGlobal = $this->calculateStudentLMD($student->grades);

    // C. On prépare les données (Utilise des noms différents pour être sûr)
    $resS1 = ['avg' => $lmdS1['average'], 'credits' => (int)$lmdS1['credits']];
    $resS2 = ['avg' => $lmdS2['average'], 'credits' => (int)$lmdS2['credits']];

    $totalCredits = (int)$lmdGlobal['credits'];
    $moyAnnuelle = floatval($lmdGlobal['average']);

    // D. On remplit l'objet final
    $student->detail_s1 = $resS1;
    $student->detail_s2 = $resS2;
    $student->average = $lmdGlobal['average'];

    // E. LOGIQUE DE DÉCISION
    if ($moyAnnuelle >= 10) {
        $student->credits = 60;
        $student->status = 'Admis';
    } elseif ($totalCredits >= 42) {
        $student->credits = $totalCredits;
        $student->status = 'Passage Cond.';
    } else {
        $student->credits = $totalCredits;
        $student->status = ($moyAnnuelle > 0) ? 'Ajourné' : 'Incomplet';
    }

    return $student;
});










    // Tri
    if ($sort === 'avg_desc') $liste = $liste->sortByDesc('average')->values();
    if ($sort === 'avg_asc') $liste = $liste->sortBy('average')->values();

    // Statistiques globales (Moyenne de tous les "comp" de l'école pour simplifier)
   $stats = [
    'total' => Student::count(),
    'moyenne_generale' => number_format(\App\Models\Grade::all()->avg('moyenne_module') ?? 0, 2),
    'eleves_a_risque' => $liste->filter(fn($s) => $s->average > 0 && $s->average < 10)->count()
];

    return Inertia::render('Dashboard', [
        'students' => $liste,
        'availableUsers' => $availableUsers,
        'subjects' => \App\Models\Subject::orderBy('name')->get(),
        'classes' => \App\Models\Classe::all(),
        'stats' => $stats,
        'filters' => $request->only(['search', 'sort'])
    ]);
}
private function calculateGroupAvg($grades) {
    if ($grades->isEmpty()) return null;
    $pts = $grades->sum(fn($g) => $g->moyenne_module * ($g->subject->coefficient ?? 1));
    $coefs = $grades->sum(fn($g) => $g->subject->coefficient ?? 1);
    return $coefs > 0 ? number_format($pts / $coefs, 2) : 0;
}

private function calculateGroupCredits($grades) {
    return $grades->filter(fn($g) => $g->moyenne_module >= 10)->sum(fn($g) => $g->subject->credits ?? 0);
}


// --- NOUVELLE FONCTION DE CALCUL GLOBAL ---
private function calculateGlobalAverage($student) {
    if ($student->grades->isEmpty()) return null;

    $totalPoints = 0;
    $totalCoeffs = 0;

    foreach ($student->grades as $grade) {
        $coeff = $grade->subject->coefficient ?? 1;
        // On utilise l'attribut que tu as créé dans le modèle Grade !
        $totalPoints += ($grade->moyenne_module * $coeff);
        $totalCoeffs += $coeff;
    }

    return $totalCoeffs > 0 ? number_format($totalPoints / $totalCoeffs, 2) : null;
}

// --- MISE À JOUR DU CALCUL SEMESTRIEL ---
private function calculateSemestreAvg($student, $semestre) {
    $grades = $student->grades->where('semestre', $semestre);
    if ($grades->isEmpty()) return null;

    $points = $grades->sum(function($g) {
        // Utilise l'attribut du modèle pour être cohérent avec ta règle (CC+Comp)/2
        return $g->moyenne_module * ($g->subject->coefficient ?? 1);
    });

    $coeffs = $grades->sum(fn($g) => $g->subject->coefficient ?? 1);
    return $coeffs > 0 ? number_format($points / $coeffs, 2) : null;
}










    // 2. Enregistrement d'un étudiant
  public function store(Request $request)
{
    $validated = $request->validate([
        'matricule' => 'required|unique:students',
        'nom' => 'required',
        'prenom' => 'required',
        'classe_id' => 'required|exists:classes,id', // On vérifie que la classe existe
    ]);

    Student::create($validated);

    return back();
}


    public function destroy(Student $student)
{
    $student->delete();
    return back();
}

public function downloadBulletin(Student $student, Request $request)
{
    $semestreNum = $request->query('semestre');

     // Vérifier s'il y a des notes avant de continuer
    $hasGrades = $student->grades()
        ->where('semestre', 'Semestre ' . $semestreNum)
        ->exists();

    if (!$hasGrades) {
        // Rediriger avec un message d'erreur si aucune note n'est trouvée
        return back()->with('error', "Impossible de générer le bulletin : aucune note n'est enregistrée pour ce semestre.");
    }
    $student->load(['grades.subject', 'classe']);

    $query = $student->grades();
    if ($semestreNum) {
        $query->where('semestre', 'Semestre ' . $semestreNum);
    }
    $grades = $query->get();

    // 1. Groupement par UE
    $ues = $grades->groupBy(function($grade) {
        return ($grade->subject->ue_code ?? 'UE') . ' - ' . ($grade->subject->ue_nom ?? 'Sans Nom');
    });

    $totalPoints = 0;
    $totalCredits = 0;
    $creditsValidesCalculés = 0;

    // 2. Calcul par UE
    foreach ($ues as $ueInfo => $items) {
        $uePts = $items->sum(fn($g) => $g->moyenne_module * ($g->subject->credits ?? 2));
        $ueCoef = $items->sum(fn($g) => $g->subject->credits ?? 2);

        $totalPoints += $uePts;
        $totalCredits += $ueCoef;

        $moyenneUE = $ueCoef > 0 ? ($uePts / $ueCoef) : 0;

        // On marque chaque note pour l'affichage PDF
        foreach ($items as $g) {
            // Une matière est acquise si : Moyenne UE >= 10 OU Moyenne Matière >= 10
            $g->is_valide = ($moyenneUE >= 10 || $g->moyenne_module >= 10);
        }

        if ($moyenneUE >= 10) {
            $creditsValidesCalculés += $ueCoef;
        } else {
            // Si l'UE échoue, on ne prend que les matières >= 10
            $creditsValidesCalculés += $items->where('moyenne_module', '>=', 10)->sum('subject.credits');
        }
    }

    $moyenne = $totalCredits > 0 ? number_format($totalPoints / $totalCredits, 2) : "0.00";
    $moyenneBrute = floatval($moyenne);

    // 3. Logique de décision
    if ($moyenneBrute >= 10) {
        $creditsAffiche = $totalCredits; // Tout est validé par compensation annuelle/semestrielle
        $resultat = "ADMIS";
    } elseif ($creditsValidesCalculés >= 42) { // Standard LMD : 42 crédits = Passage
        $creditsAffiche = $creditsValidesCalculés;
        $resultat = "PASSAGE CONDITIONNEL";
    } else {
        $creditsAffiche = $creditsValidesCalculés;
        $resultat = "AJOURNÉ";
    }

    $infoGrade = $this->getLmdGrade($moyenne);

    return Pdf::loadView('pdf.bulletin', [
        'student' => $student,
        'ues' => $ues,
        'moyenne' => $moyenne,
        'credits_valides' => $creditsAffiche,
        'total_credits' => $totalCredits,
        'resultat' => $resultat,
        'mention' => $infoGrade['mention'] ?? 'N/A',
        'grade' => $infoGrade['grade'] ?? 'E',
        'gpa' => $infoGrade['points'] ?? '0.0',
        'titre' => $semestreNum ? "RELEVÉ DE NOTES - SEMESTRE $semestreNum" : "RELEVÉ DE NOTES ANNUEL",
    ])->download("Bulletin_{$student->matricule}.pdf");
}

public function dashboard() {
    $student = Auth::user()->student()->with('classe.subjects.lessons')->first();

    return Inertia::render('Student/Dashboard', [
        'student' => $student
    ]);
}












public function update(Request $request, Student $student)
{
    $validated = $request->validate([
        'matricule' => 'required|unique:students,matricule,' . $student->id,
        'nom' => 'required',
        'prenom' => 'required',
        'classe_id' => 'required|exists:classes,id', // Correction ici
    ]);

    $student->update($validated);
    return back()->with('message', 'Étudiant mis à jour !');
}


public function linkUser(Request $request, Student $student)
{
    $request->validate([
        'user_id' => 'required|exists:users,id'
    ]);

    $student->update(['user_id' => $request->user_id]);

    return back()->with('message', 'Compte lié avec succès !');
}



// private function calculateSemestreAvg($student, $semestre) {
//     $grades = $student->grades->where('semestre', $semestre);
//     if ($grades->isEmpty()) return null;

//     $points = $grades->sum(fn($g) => $g->note * ($g->subject->coefficient ?? 1));
//     $coeffs = $grades->sum(fn($g) => $g->subject->coefficient ?? 1);

//     return $coeffs > 0 ? number_format($points / $coeffs, 2) : null;
// }
private function calculateLMDStats($student, $semestreNom)
{
    $grades = $student->grades->where('semestre', $semestreNom);
    if ($grades->isEmpty()) return ['moyenne' => null, 'credits_valides' => 0];

    // 1. On groupe les notes par UE
    $ues = $grades->groupBy(fn($g) => $g->subject->ue_code);

    $totalPointsSemestre = 0;
    $totalCreditsSemestre = 0;
    $creditsObtenus = 0;

    foreach ($ues as $ueCode => $notesDeLue) {
        $pointsUE = 0;
        $creditsUE = 0;

        foreach ($notesDeLue as $grade) {
            $c = $grade->subject->credits ?? 2;
            // Ta formule : (N1+N2+Comp)/3 ou (CC+Comp)/2 selon ton modèle Grade
            $pointsUE += ($grade->moyenne_module * $c);
            $creditsUE += $c;
        }

        $moyenneUE = $creditsUE > 0 ? ($pointsUE / $creditsUE) : 0;

        // Règle LMD : Si moyenne UE >= 10, tous les crédits de l'UE sont validés (Capitalisation)
        if ($moyenneUE >= 10) {
            $creditsObtenus += $creditsUE;
        }

        $totalPointsSemestre += $pointsUE;
        $totalCreditsSemestre += $creditsUE;
    }

    return [
        'moyenne' => $totalCreditsSemestre > 0 ? number_format($totalPointsSemestre / $totalCreditsSemestre, 2) : "0.00",
        'credits' => $creditsObtenus,
        'total_credits' => $totalCreditsSemestre
    ];
}
private function calculateStudentLMD($data) {
    // 1. Détection intelligente de la source de données
    if ($data instanceof \Illuminate\Support\Collection) {
        // Si on passe $gradesS1 (une collection filtrée), on l'utilise directement
        $grades = $data;
    } else {
        // Si on passe $student (le modèle), on prend toutes ses notes
        $grades = $data->grades;
    }

    if (!$grades || $grades->isEmpty()) {
        return ['average' => '0.00', 'credits' => 0];
    }

    $totalPointsGlobal = 0;
    $totalCoeffsGlobal = 0;
    $creditsTotauxAcquis = 0;

    // 2. Groupement par UE (Utilise ue_id ou ue_nom)
    $ueGroups = $grades->groupBy(function($grade) {
        return $grade->subject->ue_id ?? $grade->subject->ue_nom ?? 'UE-DEFAULT';
    });

    foreach ($ueGroups as $gradesInUe) {
        $uePoints = 0;
        $ueCoeffs = 0;
        $ueCreditsPotentiels = 0;

        foreach ($gradesInUe as $grade) {
            $coeff = $grade->subject->coefficient ?? $grade->subject->credits ?? 1;
            $uePoints += ($grade->moyenne_module * $coeff);
            $ueCoeffs += $coeff;
            $ueCreditsPotentiels += $grade->subject->credits ?? 0;
        }

        $moyenneUE = $ueCoeffs > 0 ? ($uePoints / $ueCoeffs) : 0;

        if ($moyenneUE >= 10) {
            $creditsTotauxAcquis += $ueCreditsPotentiels;
        } else {
            foreach ($gradesInUe as $grade) {
                if ($grade->moyenne_module >= 10) {
                    $creditsTotauxAcquis += $grade->subject->credits ?? 0;
                }
            }
        }
        $totalPointsGlobal += $uePoints;
        $totalCoeffsGlobal += $ueCoeffs;
    }

    return [
        'average' => $totalCoeffsGlobal > 0 ? number_format($totalPointsGlobal / $totalCoeffsGlobal, 2) : '0.00',
        'credits' => $creditsTotauxAcquis
    ];
}




private function getLmdGrade($moyenne) {
    $m = floatval($moyenne);

    if ($m >= 16) return ['grade' => 'A', 'mention' => 'Très Bien', 'points' => 4.0];
    if ($m >= 14) return ['grade' => 'B', 'mention' => 'Bien', 'points' => 3.5];
    if ($m >= 12) return ['grade' => 'C', 'mention' => 'Assez Bien', 'points' => 3.0];
    if ($m >= 10) return ['grade' => 'D', 'mention' => 'Passable', 'points' => 2.0];

    // Entre 8 et 10 : Échec mais potentiellement compensable
    if ($m >= 8)  return ['grade' => 'E', 'mention' => 'Médiocre', 'points' => 1.0];

    // En dessous de 8 : Échec total
    return ['grade' => 'F', 'mention' => 'Échec', 'points' => 0.0];
}


public function myGrades(Request $request)
{
    $semestre = $request->query('semestre', 'Semestre 1');
    $user = auth()->user();

    $student = Student::where('user_id', $user->id)
        ->with(['grades' => function($query) use ($semestre) {
            $query->where('semestre', $semestre)->with('subject');
        }, 'classe'])
        ->firstOrFail();

    // Groupement par UE pour l'affichage
    $gradesByUe = $student->grades->groupBy(function($grade) {
        return ($grade->subject->ue_code ?? 'UE') . ' - ' . ($grade->subject->ue_nom ?? 'Unité');
    });

    return Inertia::render('Student/GradesIndex', [
        'student' => $student,
        'gradesByUe' => $gradesByUe,
        'filters' => ['semestre' => $semestre],
        'summary' => $this->calculateStudentLMD($student)
    ]);
}

public function downloadAnnualBulletin(Student $student) {
    $student->load(['grades.subject', 'classe']);

    // On groupe les notes par semestre, PUIS par UE
    $semestres = $student->grades->groupBy('semestre');

    $dataS1 = $this->calculateStudentLMD($student->grades->where('semestre', 'Semestre 1'));
    $dataS2 = $this->calculateStudentLMD($student->grades->where('semestre', 'Semestre 2'));
    $dataGlobal = $this->calculateStudentLMD($student);

    // Détermination du résultat final
    $resultatFinal = "AJOURNÉ";
    if (floatval($dataGlobal['average']) >= 10) {
        $resultatFinal = "ADMIS";
    } elseif (intval($dataGlobal['credits']) >= 42) {
        $resultatFinal = "PASSAGE CONDITIONNEL";
    }

    $infoGrade = $this->getLmdGrade($dataGlobal['average']);

    return Pdf::loadView('pdf.annual_bulletin', [
        'student' => $student,
        'semestres' => $semestres,
        's1' => $dataS1,
        's2' => $dataS2,
        'global' => $dataGlobal,
        'resultat' => $resultatFinal,
        'mention' => $infoGrade['mention'],
        'grade' => $infoGrade['grade'],
        'gpa' => $infoGrade['points']
    ])->download("Bulletin_Annuel_{$student->matricule}.pdf");
}



}
