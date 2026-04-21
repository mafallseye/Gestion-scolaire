<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #4f46e5; text-transform: uppercase; font-size: 16px; }
        .student-info { margin-bottom: 15px; width: 100%; border: none; }
        .student-info td { border: none; padding: 2px; font-size: 10px; }

        table.main-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.main-table th { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 6px; font-size: 8px; text-transform: uppercase; }
        table.main-table td { border: 1px solid #e2e8f0; padding: 5px; text-align: center; }

        .ue-header { background-color: #f1f5f9; font-weight: bold; text-align: left !important; color: #334155; font-size: 9px; }
        /* Style pour la ligne de total UE */
        .ue-footer { background-color: #fefce8; font-weight: bold; font-size: 8px; color: #854d0e; }
        .text-left { text-align: left !important; }

        .summary-container { width: 100%; margin-top: 20px; page-break-inside: avoid; }
        .summary-box { border: 2px solid #e2e8f0; border-radius: 8px; padding: 12px; background-color: #f8fafc; text-align: center; }

        .result-badge {
            display: inline-block;
            margin-top: 10px;
            padding: 6px 15px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            background-color: {{ (str_contains($resultat, 'ADMIS') || $resultat === 'PASSAGE CONDITIONNEL') ? '#10b981' : '#ef4444' }};
        }
        /* Empêche de couper une ligne de tableau en deux */
tr { page-break-inside: avoid; }

/* Empêche de couper un bloc d'UE (Entête + Matières + Total) */
.main-table tbody { page-break-inside: auto; }

/* Force le bloc de résumé et signatures à rester ensemble */
.summary-container, .signature-block {
    page-break-inside: avoid;
}

/* Optionnel : Si le tableau est trop long, on peut réduire un peu l'interligne */
table.main-table td { padding: 4px 6px; }


        .footer-info { position: fixed; bottom: 0; width: 100%; font-size: 7px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 5px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>RÉPUBLIQUE DU SÉNÉGAL</h2>
        <p style="margin: 3px 0; font-weight: bold;">RELEVÉ DE NOTES ET RÉSULTATS ACADÉMIQUES</p>
        <span style="font-size: 11px; color: #64748b;">{{ $titre }}</span>
    </div>

    <table class="student-info">
        <tr>
            <td width="15%">Étudiant :</td>
            <td width="35%"><strong>{{ strtoupper($student->nom) }} {{ $student->prenom }}</strong></td>
            <td width="15%">Classe :</td>
            <td width="35%"><strong>{{ $student->classe->nom_classe ?? 'N/A' }}</strong></td>
        </tr>
        <tr>
            <td>Matricule :</td>
            <td><strong>{{ $student->matricule }}</strong></td>
            <td>Année :</td>
            <td><strong>2025 - 2026</strong></td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th class="text-left" width="28%">Unités d'Enseignement / Éléments</th>
                <th width="6%">Crédits</th>
                <th width="7%">N1 (CC)</th>
                <th width="7%">N2 (CC)</th>
                <th width="8%">Moy CC</th>
                <th width="8%">Exam.</th>
                <th width="8%">Rattrap.</th>
                <th width="12%">Moy/20</th>
                <th width="16%">Validation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ues as $ueLabel => $ecList)
                {{-- Entête de l'UE --}}
                <tr class="ue-header">
                    <td colspan="9" style="padding-left: 8px;">{{ $ueLabel }}</td>
                </tr>

                {{-- Liste des matières (EC) --}}
                @foreach($ecList as $item)
                <tr>
                    <td class="text-left" style="font-size: 9px; padding-left: 15px;">{{ $item->subject->name }}</td>
                    <td style="font-weight: bold;">{{ $item->subject->credits ?? 0 }}</td>
                    <td style="color: #64748b;">{{ number_format($item->note1, 2) }}</td>
                    <td style="color: #64748b;">{{ number_format($item->note2, 2) }}</td>
                    <td style="background-color: #f8fafc;">{{ number_format(($item->note1 + $item->note2) / 2, 2) }}</td>
                    <td>{{ $item->note_composition ?? '-' }}</td>
                    <td style="color: {{ $item->note_rattrapage ? '#4f46e5' : '#cbd5e1' }};">
                        {{ $item->note_rattrapage ?? '-' }}
                    </td>
                    <td style="font-weight: bold; background-color: #f8fafc;">
                        {{ number_format($item->moyenne_module, 2) }}
                    </td>
                    <td>
                        <span style="color: {{ $item->is_valide ? '#10b981' : '#ef4444' }}; font-weight: bold; font-size: 8px;">
                            {{ $item->is_valide ? 'V.C (ACQUIS)' : 'NON ACQUIS' }}
                        </span>
                    </td>
                </tr>
                @endforeach

                {{-- LIGNE TOTAL UE --}}
                @php
                    $ueMoyenne = $ecList->sum(fn($g) => $g->moyenne_module * ($g->subject->credits ?? 1)) / max($ecList->sum('subject.credits'), 1);
                    $ueCreditsTotal = $ecList->sum('subject.credits');
                @endphp
                <tr class="ue-footer">
                    <td class="text-left" style="padding-left: 10px; text-transform: uppercase;">Sous-total {{ explode(' - ', $ueLabel)[0] }}</td>
                    <td>{{ $ueCreditsTotal }}</td>
                    <td colspan="5" style="text-align: right; padding-right: 10px;">Moyenne UE :</td>
                    <td style="background-color: #fef9c3;">{{ number_format($ueMoyenne, 2) }}</td>
                    <td>{{ $ueMoyenne >= 10 ? 'VALIDÉE' : 'NON VALIDÉE' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-container">
        <tr>
            <td width="55%" style="vertical-align: top; padding-right: 15px; text-align: left;">
                <p style="font-size: 8px; color: #64748b; text-align: justify;">
                    <strong>Note LMD :</strong> La validation d'une Unité d'Enseignement (UE) entraîne la capitalisation intégrale des crédits associés. En cas d'échec à l'UE, seules les matières (EC) ayant une moyenne supérieure ou égale à 10/20 sont capitalisées. Le passage conditionnel est autorisé avec un minimum de 42 crédits sur 60.
                </p>
            </td>
            <td width="45%">
                <div class="summary-box">
                    <div style="font-size: 9px; text-transform: uppercase; color: #64748b;">Résultat Général</div>
                    <div style="font-size: 22px; font-weight: bold; color: #1e293b; margin: 5px 0;">
                        {{ $moyenne }} <small style="font-size: 10px;">/ 20</small>
                    </div>

                    <div style="border-top: 1px solid #e2e8f0; padding-top: 8px; margin-top: 5px;">
                        <span style="font-weight: bold;">Mention : {{ $mention }}</span><br>
                        <span style="font-size: 9px; color: #475569;">Grade : <strong>{{ $grade }}</strong> | GPA : <strong>{{ number_format($gpa, 1) }}</strong></span>
                    </div>

                    <div style="margin-top: 10px; font-size: 10px;">
                        Crédits Capitalisés : <strong>{{ $credits_valides }} / {{ $total_credits }}</strong>
                        <div style="width: 100%; background: #e2e8f0; height: 5px; border-radius: 3px; margin-top: 4px;">
                            <div style="width: {{ min(($credits_valides / max($total_credits, 1)) * 100, 100) }}%; background: #10b981; height: 5px; border-radius: 3px;"></div>
                        </div>
                    </div>

                    <div class="result-badge">
                        {{ $resultat }}
                    </div>
                </div>
            </td>
        </tr>
    </table>
<!-- BLOC SIGNATURES -->
<table style="width: 100%; margin-top: 40px; border: none;">
    <tr>
        <td style="width: 50%; border: none; text-align: left; vertical-align: top;">
            <p style="font-size: 9px; margin-bottom: 40px;">L'étudiant(e),</p>
            <div style="width: 150px; border-bottom: 1px dotted #cbd5e1;"></div>
            <p style="font-size: 7px; color: #94a3b8; margin-top: 5px;">(Signature pour réception)</p>
        </td>
        <td style="width: 50%; border: none; text-align: right; vertical-align: top;">
            <p style="font-size: 9px; font-weight: bold; margin-bottom: 5px;">Le Directeur des Études,</p>
            <p style="font-size: 8px; color: #64748b; margin-bottom: 40px;">Fait à Dakar, le {{ date('d/m/Y') }}</p>

            <!-- Emplacement pour le cachet -->
            <div style="display: inline-block; width: 100px; height: 100px; border: 2px dashed #e2e8f0; border-radius: 50%; text-align: center; line-height: 100px; color: #e2e8f0; font-size: 8px; text-transform: uppercase;">
                Cachet de l'École
            </div>

            <p style="font-size: 10px; font-weight: 900; color: #1e293b; margin-top: 10px; text-transform: uppercase;">
                M. [NOM DU DIRECTEUR]
            </p>
        </td>
    </tr>
</table>

    <div class="footer-info">
        Document officiel généré le {{ date('d/m/Y à H:i') }} • EduManager v2.0 • Dakar, Sénégal
    </div>
</body>
</html>
