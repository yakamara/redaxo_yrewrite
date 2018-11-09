Changelog
=========

Version 2.4.0 – 03.11.2018
--------------------------

### Bugs

- Startartikel in Nicht-Startsprachen: Korrekten Suffix nutzen
- htaccess Anpassungen dots angepasst (Alexander Walther / alexplusde)
- EP Aufrufe ergänzt (ART_MOVED, ART_COPIED, CAT_MOVED) (Alex Wenz)
- Not Found Artikel nun auch in der richtigen Sprache
- Warnings entfernt
- Beim löschen einer Sprachen kam es bei yrewrite zu einen Error (Tobias Kreis)

### Neu

- headerstatus um 302 bei Weiterleitungen ergänzt (Wolfgang Bund)
- Ermöglicht, nur ein Zeichen in der URL einzugeben, das nicht mit einem Slash beginnt. Slash am Ende wird dennoch geblockt. (Alexander Walther / alexplusde)
- Statuscode nun auch in der Weiterleitungsübersicht (Wolfgang Bund)
- HTTP_X_FORWARDED_SERVER wird nun beachtet, falls z.B. ein Loadbalancer vorhanden ist
- Sprachen ergänzt: spanisch, englisch, schwedisch
- neue Methode rex_yrewrite::isInCurrentDomain($ArticleId)

### Docs

- Diverse Anpassungen (Alexander Walther / alexplusde)



Version 2.3.0 – 30.01.2018
--------------------------

### Info

- REDAXO 5.5 ist Vorraussetzung

### New
- Testlink in Übersicht gebaut
- Domain werden nun sortiert aufgelistet
- Fremdpages werden nun richtig in die Subnavi eingebunden
- Sprachen ergänzt und angepasst Danke ynamite, Ferando Averanga, Tina Soares, Jürgen Weiss
- Alte ComAuth Abfrage entfernt
- unbekannte Zeichen werden über iconv entfernt, bekannte Zeichen erweitert Danke Tobias Kreis
- Formularname festgelegt
- CSRF Protection eingebaut
- Dokumentation ergänzt. Danke Alex Walther und Thomas Skerbis
- Texte an diversen Stellen gekürzt und angepasst
- Sitemapausgabe hat nun einen cleanOutputBuffer
- URL Umbruch verbesser in der Artikel-URL-Ansicht
- Bei Metadescription werden nun die Inhalte ge-strip_taged
- Aufruf von media mit mediatypes in .htaccess 
- Artikel-SEO: Placeholder wird bei Title angezeigt


### Bugs
- Installationsbug behoben
- Offlinesprache wird nun aus der Sitemap entfernt Danke Alex Platter
- Bei URL Generierung werden nun nur aktive Sprache unterstützt
- Warnings bei Konsolenaufrufen entfernt
- Forwarded Protokoll wird nun beachtet (Load Balancer Problem)
- Diverse Warnings entfernt


Version 2.2.0 – 15.09.2016
--------------------------

### Bugs
- Weiterleitungen funktionierten unter bestimmten Situationen nicht
- Änderungen an Domainnamen werden nun konsequent referenziert.


Version 2.1.0 – 25.08.2016
--------------------------

### Neu
- Diverse Beschreibung/Erklärungen ergänzt
- Methode getCurrentDomain ergänzt
- Diverse Sonderzeichen bei Ersetzungen ergänzt
- Schwedische Sprache ergänzt
- HreflangTag geändert
- extension point "YREWRITE_HREFLANG_TAGS" hinzugefügt
- robots.txt darf nun auch leer sein
- Custom-Urls nun auch mit "/" am Ende erlaubt
- Optional kann nun bei der Startsprache der language slug ausgeschaltet werden
- rex::getServer() wird nicht mehr überschrieben
- www. und http/s werden je nach konfigurierter Domain automatisch umgeleitet
- getFullUrlByArticleId um params und separator wie bei rex_getUrl ergänzt

### Bugs
- Startseitenerkennung korrigiert.
- Automatische Weiterleitung bei fehlendem Suffix
- Sitemap wird nun auch bei der default Domain ausgegeben
- Default clang immer auf aktuelle gesetzt
- ETag deaktiviert
- htaccess. files Ordner auf media geändert
- "Doppelter Slash"-Problem korrigiert
- Sitemap enthält keine 404 Seiten mehr
- Pfadaufruf für Windows angepasst
- Bestimmte Weiterleitung hatten nicht funktioniert.
- Weiterleitung auf Dateien ist korrigiert
- Umgang mit der default-Domain verbessert

### Info
- Textänderung: Startpunkt -> Mountpoint
- Diverse fehlende Texte ergänzt


Version 2.0.1 – 11.02.2016
--------------------------

- Texte ergänzt / deutsch und englisch
- rex_yrewrite_scheme ergänzt um getSuffix() und aufruf von normalize() erlaubt und getScheme() ergänzt
- Fehlerinfo wenn noch keine Domain vorhanden ist.

### Bugs

- Caching in der htaccess angepasst. Ist zu unkontrolliert und allgemein. Deswegen erstmal draußen
- Workaround für RewriteBase, allgemeingültig gemacht


Version 2.0.0 – 02.02.2016
--------------------------

- Portierung zu REDAXO 5
- Unterordner sind nun möglich
- http oder https, wird direkt in der Domainbezeichnung festgelegt
- Meta langhref ergänzt
- Fürs Verständnis aus "undefined" -> "default" gemacht.
- Standard-URL-Schema geändert .. aus .html -> /
- Canonical Urls ergänzt

