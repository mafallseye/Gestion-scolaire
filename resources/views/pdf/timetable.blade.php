<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emploi du temps - {{ $classe->nom_classe }}</title>
    <style>
        /* CONFIGURATION GENERALE */
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #1e293b; margin: 0; padding: 0; }

        /* LOGO & EN-TETE */
        .header-table { width: 100%; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; }
        .logo-icon {
            background-color: #4f46e5; color: white; width: 28px; height: 28px;
            line-height: 28px; text-align: center; border-radius: 6px;
            display: inline-block; font-size: 16px; margin-right: 8px;
        }
        .logo-text { font-size: 20px; font-weight: bold; display: inline-block; vertical-align: middle; }
        .text-indigo { color: #4f46e5; }
        .text-slate { color: #1e293b; }

        /* INFOS CLASSE */
        .class-info { margin-bottom: 15px; }
        .class-title { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .class-subtitle { color: #64748b; font-size: 11px; margin-left: 10px; }

        /* GRILLE DU CALENDRIER */
        table.calendar { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .calendar th {
            background-color: #0f172a; color: white; padding: 10px 5px;
            text-transform: uppercase; font-size: 8px; letter-spacing: 1px;
            border: 1px solid #0f172a;
        }
        .calendar td {
            border: 1px solid #e2e8f0; vertical-align: top; padding: 6px;
            height: 500px; background-color: #fcfdfe;
        }

        /* CARTES DE COURS */
        .course-card {
            background-color: #ffffff; border-left: 3px solid #4f46e5;
            padding: 8px; margin-bottom: 8px; border-radius: 4px;
            border-top: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;
        }
        .course-name { font-weight: bold; color: #1e293b; text-transform: uppercase; font-size: 8px; margin-bottom: 3px; }
        .course-time { color: #4f46e5; font-size: 8px; font-weight: bold; }
        .course-room { margin-top: 5px; color: #64748b; font-size: 7px; font-weight: bold; text-transform: uppercase; }

        /* PIED DE PAGE */
        .footer { position: fixed; bottom: -10px; width: 100%; text-align: right; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>

    <!-- EN-TETE AVEC LOGO -->
    <table class="header-table">
        <tr>
            <td width="60%">
                <div class="logo-icon">🎓</div>
                <div class="logo-text">
                    <span class="text-slate">Edu</span><span class="text-indigo">Manager</span>
                </div>
            </td>
            <td width="40%" style="text-align: right; vertical-align: middle;">
               <div style="font-weight: bold; color: #64748b; font-size: 9px; text-transform: uppercase;">
                    Emploi du Temps Officiel<br>
                    <span style="color: #4f46e5;">SESSION {{ $academicSession ?? '2025 - 2026' }}</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- TITRE DE LA CLASSE -->
    <div class="class-info">
        <span class="class-title">{{ $classe->nom_classe }}</span>
        <span class="class-subtitle">| Niveau : {{ $classe->niveau }}</span>
    </div>

    <!-- GRILLE DE LA SEMAINE -->
    <table class="calendar">
        <thead>
            <tr>
                @foreach($days as $day)
                    <th>{{ $day }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach($days as $day)
                    <td>
                        @if(isset($schedule[$day]))
                            @foreach($schedule[$day] as $course)
                                <div class="course-card">
                                    <div class="course-name">{{ $course->subject->name }}</div>
                                    <div class="course-time">
                                        {{ date('H:i', strtotime($course->start_time)) }} - {{ date('H:i', strtotime($course->end_time)) }}
                                    </div>
                                    @if($course->room)
                                        <div class="course-room">📍 {{ $course->room }}</div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Document officiel généré le {{ date('d/m/Y à H:i') }} • EduManager Administration
    </div>

</body>
</html>
