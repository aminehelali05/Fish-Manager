# Adminer — importer la base de données 🗄️

1. Téléchargez `adminer.php` depuis https://www.adminer.org/ (version PHP seule, fichier unique).
2. Placez `adminer.php` à la racine du projet (c:\xampp\htdocs\Fish_manger\adminer.php) et commitez le fichier si vous le souhaitez.
3. Poussez sur GitHub et déployez sur Render.
4. Ouvrez `https://votre-app.onrender.com/adminer.php` et renseignez :
   - Server: la valeur `DB_HOST` fournie par Render
   - Username: `DB_USER`
   - Password: `DB_PASS`
   - Database: `DB_NAME`
5. Importez votre fichier `.sql` via l'interface d'Adminer.

Remarque : Adminer est pratique et léger, **supprimez-le** ou protégez-le après l'import si le site est en production.
