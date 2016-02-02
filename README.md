redaxo_yrewrite
================

Ein Multidomain-Rewriter Addon für REDAXO CMS >= 5.0

Mit diesem AddOn lassen sich verschiedenen Domains innerhalb von REDAXO
bestimmten Kategorien, auch sprachabhängig, zuweisen.

Somit kann man mehrere Webseiten in einer Installation verwalten.
Jede Domain kann eine eigene Startsprache haben oder für bestimmte
Sprachen nur zuständig sein.
Es wäre z.B. auch möglich bei englisch nur eine .com Domain zu verwenden, und bei
deutsch die entsprechende .de Domain. Oder auch nur eine Domain mit
Hauptsprache englisch. Die deutschen URL würden dann auf der englischen
Domain mit Sprachkürzel dargestellt werden.


Funktionsliste
-------

* Mehrere Domains in einer Webseite verwaltbar
* Sprachabhängigkeiten von Domains zuweisbar
* SEO Features: Domain- und sprachabhängige robots und sitemap Dateien
* Individuelle URL pro Artikel möglich
* Seitentitel Schema definierbar / pro Domain/Sprache
* Alias Domains die auf die Hauptdomain verweisen
* Allgemeine Weiterleitungen. URLs zu internen Artikeln, Dateien, externen Artikeln
* Canonical Urls

Installation
-------

* Release herunterladen und entpacken.
    * Die aktuelle github Version nur als Entwicklerversion betrachten
* Umbenennen in yrewrite
* In den REDAXO 5 AddOnordner legen /redaxo/src/addons/

oder

* Über das REDAXO Backend installieren und aktivieren
* Im YRewriter die Domain/s eintragen und zuweisen


Last Changes
-------

### Version 2.0 // 02.02.2016

- Portierung zu Redaxo 5
- Unterordner sind nun möglich
- http oder https, wird direkt in der Domainbezeichnung festgelegt
- Meta langhref ergänzt
- Fürs Verständnis aus "undefined" -> "default" gemacht.
- Standard-URL-Schema geändert .. aus .html -> /
- Canonical Urls ergänzt

