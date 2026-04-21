<!-- Structure simplifiée du tableau annuel -->
<table class="main-table">
    <thead>
        <tr>
            <th>Désignation</th>
            <th>Moyenne</th>
            <th>Crédits Acquis</th>
            <th>Résultat</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-left">RÉSULTATS SEMESTRE 1</td>
            <td><strong>{{ $s1['average'] }}</strong></td>
            <td>{{ $s1['credits'] }} / 30</td>
            <td>{{ floatval($s1['average']) >= 10 ? 'VALIDÉ' : 'NON VALIDÉ' }}</td>
        </tr>
        <tr>
            <td class="text-left">RÉSULTATS SEMESTRE 2</td>
            <td><strong>{{ $s2['average'] }}</strong></td>
            <td>{{ $s2['credits'] }} / 30</td>
            <td>{{ floatval($s2['average']) >= 10 ? 'VALIDÉ' : 'NON VALIDÉ' }}</td>
        </tr>
        <tr style="background: #f1f5f9; font-weight: bold;">
            <td class="text-left">BILAN ANNUEL GLOBAL</td>
            <td><strong>{{ $global['average'] }}</strong></td>
            <td>{{ $global['credits'] }} / 60</td>
            <td>{{ $resultat }}</td>
        </tr>
    </tbody>
</table>

<!-- Bloc décision finale -->
<div class="summary-box" style="margin-top: 30px; border: 2px solid #1e293b; text-align: left; padding: 20px;">
    <h3 style="text-transform: uppercase; text-decoration: underline; margin-bottom: 10px;">Décision du Jury</h3>

    <p style="font-size: 11px;">
        Au vu des résultats obtenus lors des sessions normales et/ou de rattrapage,
        l'étudiant est déclaré :
        <strong style="font-size: 14px; text-transform: uppercase;">{{ $resultat }}</strong>
    </p>

    <p style="margin-top: 10px;">
        Observations :
        @if($resultat === 'PASSAGE CONDITIONNEL')
            L'étudiant est autorisé à s'inscrire en année supérieure avec une dette de
            <strong>{{ 60 - $global['credits'] }} crédits</strong> à régulariser.
        @elseif($resultat === 'ADMIS')
            L'étudiant a validé l'intégralité de son année universitaire.
        @else
            L'étudiant n'est pas autorisé à passer en année supérieure.
        @endif
    </p>
</div>
