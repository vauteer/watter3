## Über Watter3

'Watter3' ist eine Webanwendung für Turniere des Kartenspiels 'Watten'
Es ist eine 'Single-Page-Application' und kann per Browser von verschiedenen Endgeräten benutzt werden.
### Enthaltene Funktionen:
- Erstellen von Turnieren
- Eingabe der Teilnehmer (Einzeln oder Teams)
- Erstellung des Spielplans
- Erstellung von Tischlisten (zum Schreiben der Punkte)
- Eingabe der Ergebnisse
- Automatische Erstellung der Rangliste
- Benutzerverwaltung mit Rechte-Management (Rollen)
- Automatische und manuelle Backups
## Voraussetzungen
1. PHP 8.1+
2. Composer
3. Node (mit npm)
4. MySQL 5.7+ oder MariaDB 10.10+

## Installation
1. Das Projekt clonen/installieren
2. In das Projekt-Verzeichnis wechseln
3. Die Konfigurations-Datei erzeugen<br>cp .env.example .env
1. composer install --optimize-autoloader --no-dev
4. php artisan key:generate
5. php artisan storage:link
5. Eine leere MySQL Datenbank erzeugen
7. Die Konfigurations-Datei anpassen (Datenbank, Titel, ...)
8. php artisan migrate
9. Einen Administrator anlegen<br>php artisan app:user 'Max Mustermann' 'max@mustermann.de' --password=******** --admin
9. npm install
10. npm run build
11. Zum Testen: php artisan serve
11. Eine Domain/Subdomain einrichten<br>Dokumentenstamm ist das 'public' Verzeichnis!
12. Mit den Administrator-Daten anmelden

## Wiederherstellen eines Backups
Machen Sie ein Backup, falls sie Daten überschreiben!
1. Installieren der Anwendung, falls nötig
2. Das Löschen der aktuellen Daten ist nicht nötig, weil die komplette Datenbank überschrieben wird!
2. Mit phpMyAdmin oder ähnlichen Programmen das Export-Script importieren.

## Benutzte Frameworks und Tools
- [Laravel](https://laravel.com)
- [Vue.js](https://vuejs.org)
- [Inertia.js](https://inertiajs.com)
- [tailwindcss](https://tailwindcss.com)
- [Vite](https://vitejs.dev)
- und viele mehr

## So schaut die Web-Anwendung in der Praxis aus:
- [Meine Turniere](https://watter.it-rules.de)

This web application is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
