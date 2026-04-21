# Système de Gestion Pédagogique (LMD)

Ce projet est une application web de gestion scolaire développée dans le cadre de mon mémoire de Master. Elle permet de gérer les inscriptions, les notes, et le calcul automatique des moyennes selon le système LMD (Licence-Master-Doctorat).

## 🚀 Technologies utilisées

- **Backend** : Laravel 10 / 11
- **Frontend** : Vue.js 3 avec Inertia.js
- **Styling** : Tailwind CSS
- **Base de données** : MySQL
- **Authentification** : Laravel Breeze / Sanctum

## ✨ Fonctionnalités clés

- **Gestion des utilisateurs** : Administration des étudiants, enseignants et administrateurs.
- **Système LMD** : Gestion des Unités d'Enseignement (UE) et des Éléments Constitutifs (EC).
- **Calcul automatique** : Calcul des moyennes pondérées, gestion des crédits ECTS et des compensations.
- **Tableau de bord** : Statistiques en temps réel sur les effectifs et les résultats.

## 🛠️ Installation locale

1. **Cloner le projet** :
   ```bash
   git clone https://github.com/mafallseye/Gestion-scolaire
   cd Gestion-scolaire
   
2. **Installer les  dependance PHP**:
  ```bash
composer install

3. **Installer  les dépendances JS**:
  ```bash
npm install && npm run dev

4. **Configuration de l'environnement** :
Créer une copie du fichier .env.example et la nommer .env.
Configurer les accès à votre base de données MySQL dans le fichier .env.

5. ** Migration de la Base de données**:
php artisan migrate

6. **Lancer le serveur**:
php artisan serve


##📝 Auteur
Maguette Fall SEYE - Étudiant en Master Informatique .
Sous la direction de M. Abdourahmane Balde.
