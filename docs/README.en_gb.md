# YRewrite

## Übersicht

Dieses Addon bietet eine Möglichkeit, REDAXO mit mehreren Domains zu betreiben. Mehrere Domains können dann sinnvoll sein, wenn

* mehrere Websites eines Kunden in einer Installation verwaltet werden,
* verschiedene Sprachen (`clang`) einer Website unter unterschiedlichen Domains oder Subdomains erreichbar sind,
* oder beides.

> Tipp: Wir empfehlen im ersten Fall, für jede einzelne Domain in der Struktur auf der obersten Ebene eine Kategorie anzulegen.

## Features

* Mehrere Domains in einer Webseite verwaltbar
* Sprachabhängigkeiten von Domains zuweisbar
* SEO Features: Domain- und sprachabhängige robots und sitemap Dateien
* Individuelle URL pro Artikel möglich
* Seitentitel Schema definierbar / pro Domain/Sprache
* Alias Domains die auf die Hauptdomain verweisen
* Allgemeine Weiterleitungen. URLs zu internen Artikeln, Dateien, externen Artikeln
* Canonical Urls

## Installation

Voraussetzung für die aktuelle Version von YRewrite: REDAXO >= 5.5

* Über das REDAXO-Backend installieren und aktivieren
* Setup ausführen

# Erste Schritte

## Setup

Nach der Installation und dem Abschluss des Setups wird die `.htaccess`-Datei von REDAXO aktualisiert. Auch eine virtuelle `robots.txt` und `sitemap.xml` werden erstellt.

Anschließend können ein oder mehrere Domains zu YRewrite hinzugefügt werden.

## Domain hinzufügen

1. In "YRewrite" unter "Domains" Auf das +-Zeichen klicken.
2. Domain eintragen, bspw. `https://www.meine-domain.de/`.
3. Mount-Artikel auswählen. Das ist der der Startartikel einer Kategorie, in der sich YRewrite einklinken soll. Alle Artikel unterhalb des Mount-Artikels sind dann über die Domain aufrufbar.
4. Startseiten-Artikel auswählen. Das kann der Mount-Artikel sein oder eine separate Artikelseite. Diese wird als Startseite der Domain aufgerufen.
5. Fehlerseiten-Artikel auswählen. Das ist der Artikel, der mit einem 404-Fehlercode ausgegeben wird, z.B., wenn eine Seite nicht gefunden werden kann oder ein Tippfehler in der Adresse vorliegt.
6. Spracheinstellungen: Hier können Sprachen ausgewählt werden, die mit der Domain verknüpft werden. So lassen sich bspw. unterschiedliche Domains pro Sprache umsetzen.
7. Titelschema eintragen, bspw. `%T - Meine Domain`. Dieses Titelschema kann dann im Website-Template ausgegeben werden.
8. robots.txt-Einstellungen hinzufügen. Siehe Tipp unten.
9. Domain hinzufügen.

Diese Vorgehensweise für alle gewünschten Domains wiederholen.

> Tipp: Um die Installation während der Entwicklung zuverlässig gegen ein Crawling von Bots und Suchmaschinen zu schützen, genügt die `robots.txt` nicht. Dazu gibt es das `maintanance`-Addon von https://friendsofREDAXO.github.io

> Tipp: Die Domain auch in der Google Search Console hinterlegen und die `sitemap.xml` dort hinzufügen, um das Crawling zu beschleunigen. Die Domain sollte in allen vier Variationen hinterlegt werden, also mit/ohne `https` und mit/ohne `www.`. Die `sitemap.xml` jedoch nur in der Hauptdomain, am besten mit `https://` und `www.`

> Hinweis: Domains mit Umlauten bitte derzeit decodiert eintragen. Umwandlung bspw. mit https://www.punycoder.com

## Alias-Domain hinzufügen

Alias-Domains werden nur dann benötigt, wenn mehrere Domains auf den selben Ordner im Server zeigen, aber keine separaten Websites aufrufen. z.B. `www.meinedomain.de` und `www.meine-domain.de`.

Alias-Domains müssen nicht eingetragen werden, wenn die Domain nicht auf das Serververzeichnis zeigt. Einige Hoster bieten bspw. von sich aus die Möglichkeit, per Redirect von `www.meinedomain.de` auf `www.meine-domain.de` weiterzuleiten. Dann wird die Einstellung nicht benötigt.

1. In "YRewrite" unter "Domains" Auf das +-Zeichen klicken
2. Alias-Domain eintragen, bspw. `https://www.meine-domain.de/`
3. Ziel-Domain aus YRewrite auswählen
4. Alias-Domain hinzufügen

## Weiterleitungen

Unter Weiterleitungen können URLs definiert werden, die dann auf einen bestimmten Artikel oder eine andere Adresse umgeleitet werden.

> **Hinweis:** Mit dieser Einstellung können nicht bereits vorhandene Artikel / URLs umgeleitet werden, sondern nur URLs, die in der REDAXO-Installation nicht vorhanden sind. Das ist bspw. bei einem Relaunch der Fall, wenn alte URLs auf eine neue Zielseite umgeleitet werden sollen.

## Setup

Unter `Setup` kann die `.htaccess`-Datei neu überschrieben werden, die für die Verwendung von YRewrite benötigt wird. Außerdem sind die `sitemap.xml` und `robots.txt` je Domain einsehbar.


# Klassen-Referenz

## YRewrite-Objekt

Siehe auch: https://github.com/yakamara/REDAXO_yrewrite/blob/master/lib/yrewrite.php

```
    $yrewrite = new rex_yrewrite;
    # dump($yrewrite); // optional alle Eigenschaften und Methoden anzeigen
```

**Methoden**

```
```

## YRewrite-Domain-Objekt

Siehe auch: https://github.com/yakamara/REDAXO_yrewrite/blob/master/lib/domain.php

```
$domain = rex_yrewrite::getCurrentDomain();
dump($domain); // optional alle Eigenschaften und Methoden anzeigen
```

**Methoden**
```
init()
getScheme()
setScheme(rex_yrewrite_scheme $scheme)
addDomain(rex_yrewrite_domain $domain)
addAliasDomain($from_domain, $to_domain_id, $clang_start = 0)
getDomains()
getDomainByName($name)
getDomainById($id)
getDefaultDomain()
getCurrentDomain()
getFullUrlByArticleId($id, $clang = null, array $parameters = [], $separator = \'&amp;\')
getDomainByArticleId($aid, $clang = null)
getArticleIdByUrl($domain, $url)
isDomainStartArticle($aid, $clang = null)
isDomainMountpoint($aid, $clang = null)
getPathsByDomain($domain)
prepare()
rewrite($params = [], $yparams = [], $fullpath = false)
generatePathFile($params)
checkUrl($url)
generateConfig()
readConfig()
readPathFile()
copyHtaccess()
isHttps()
deleteCache()
getFullPath($link = \'\')
getHost()
```
## YRewrite-SEO-Objekt

Siehe auch: https://github.com/yakamara/REDAXO_yrewrite/blob/master/lib/seo.php


```
$seo = new rex_yrewrite_seo();
dump($seo); // optional alle Eigenschaften und Methoden anzeigen
```

**Methoden**

```
```

# Beispiele

## ID der aktuellen Domain in YRewrite

```
rex_yrewrite::getCurrentDomain()->getId();

```

Beispiel-Rückgabewert: `1`

## Mount-ID der Domain

```
rex_yrewrite::getCurrentDomain()->getMountId();
```

Beispiel-Rückgabewert: `5`


## Startartikel-ID der Domain

```
rex_yrewrite::getCurrentDomain()->getStartArticleId();
```

Beispiel-Rückgabewert: `42`

## Fehler-Artikel-ID der Domain

```
rex_yrewrite::getCurrentDomain()->getNotfoundId();
```

Beispiel-Rückgabewert: `43`

## Name der aktuellen Domain

```
rex_yrewrite::getCurrentDomain()->getName();
```

Beispiel-Rückgabewert: `meine-domain.de`

## vollständige URL eines Artikels

```
rex_yrewrite::getFullUrlByArticleId(42);
```

Beispiel-Rückgabewert: `https://www.meine-domain.de/meine-kategorie/mein-artikel.html`

## Zu welcher Domain gehört der aktuelle Artikel?

```
rex_yrewrite::getDomainByArticleId(REX_ARTICLE_ID)->getName();
```

Beispiel-Rückgabewert: `meine-domain.de`

## Meta-Tags auslesen (`description`, `title`, usw.)

Diesen Codeabschnitt in den `<head>`-Bereich des Templates kopieren:

```
$seo = new rex_yrewrite_seo();
echo $seo->getTitleTag();
echo $seo->getDescriptionTag();
echo $seo->getRobotsTag();
echo $seo->getHreflangTags();
echo $seo->getCanonicalUrlTag();
```

## Navigation Factory in Abhängigkeit der gewählten Domain

Weitere Informaionen zur Navigation Factory des REDAXO-Cores in der API-Dokumentation unter https://REDAXO.org/api/master/ und bei den Tricks von FriendsOfREDAXO: https://github.com/friendsofREDAXO/tricks/

```
$nav = rex_navigation::factory();
echo $nav->get(rex_yrewrite::getCurrentDomain()->getMountId(), 1, TRUE, TRUE);
```

## Übersicht aller Domains ausgeben

```
$domains = array_filter(rex_sql::factory()->setDebug(0)->query(\'SELECT * FROM rex_yrewrite_domain\')
foreach($domains as $domain) {
    dump($domain);
}
```

# URL-Schemes für YRewrite

## Übersicht

YRewrite kann durch Schemes erweitert werden.

**Installation**
- Als Datei im `lib`-Ordner des __project-AddOns__ ablegen.
- Dateiname: `eigene_rewrite_class.php`
- In die `boot.php` des project-AddOns einsetzen:

```php
<?php
if (rex_addon::get(\'yrewrite\')->isAvailable()) {
    rex_yrewrite::setScheme(new eigene_rewrite_class());
}
```

Nachfolgend listen wir hier ein paar Beispiele.

## Endung auf .html setzen

```php
<?php
class rex_yrewrite_scheme_mysuffix extends rex_yrewrite_scheme
{
    protected $suffix = \'.html\';
}
```

## Trailing Slash entfernen

```php
<?php
class rex_yrewrite_scheme_mysuffix extends rex_yrewrite_scheme
{
    protected $suffix = Null;
}
```

## URL-Replacer

Ersetzt URLs leerer Elternkategorien mit den URLs der nächsten mit inhalt versehenen (online-)Kindkategorie.

> Basiert auf: https://gist.github.com/gharlan/a70704b1c309cb1281c1

### Weiterleitung egal ob Inhalt in Startartikel der Elternkategorie

```php
<?php
class rex_yrewrite_scheme_gh extends rex_yrewrite_scheme
{
    protected $suffix = \'/\';

    public function getRedirection(rex_article $art, rex_yrewrite_domain $domain)
    {

        if ($art->isStartArticle() && ($cats = $art->getCategory()->getChildren(true))) {
            return $cats[0];
        }

        return false;
    }
}
```

### Weiterleitung nur wenn kein Inhalt im Startartikel der Elternkategorie

```php
<?php
class rex_yrewrite_scheme_gh extends rex_yrewrite_scheme
{
    protected $suffix = \'/\';

    public function getRedirection(rex_article $art, rex_yrewrite_domain $domain)
    {

        if ($art->isStartArticle() && ($cats = $art->getCategory()->getChildren(true)) && !rex_article_slice::getFirstSliceForCtype(1, $art->getId(), rex_clang::getCurrentId())) {
            return $cats[0];
        }

    return false;
    }
}
```

## URL manipulieren, hier mit dem AddOn Sprog

So kann als Kategoriename ein Platzhalter wie {{contact}} verwendet werden und durch die in Sprog hinterlegten Sprachvarianten ersetzt werden.

One Level, Kategoriename-Ersetzung durch Sprog.

```php
<?php
class translate_url_with_sprog extends rex_yrewrite_scheme
{

    public function appendCategory($path, rex_category $cat, rex_yrewrite_domain $domain)
    {
        return $path;
    }

    public function appendArticle($path, rex_article $art, rex_yrewrite_domain $domain)
    {
        return $path . \'/\' . $this->normalize(sprogdown($art->getName(), $art->getClangId()), $art->getClangId()) . \'/\';
    }
}
```

Multilevel, Kategoriename-Ersetzung durch Sprog.

```php
<?php
class translate_url_with_sprog extends rex_yrewrite_scheme
{
    public function appendCategory($path, rex_category $cat, rex_yrewrite_domain $domain)
    {
        return $path . \'/\' . $this->normalize(sprogdown($cat->getName(), $cat->getClangId()), $cat->getClangId());
    }
}
```

## Addons, die eigene Schemes mitbringen:

- YRewrite scheme: https://github.com/FriendsOfREDAXO/yrewrite_scheme
- xcore: https://github.com/RexDude/xcore


# Links und Hilfe

## Bugmeldungen Hilfe und Links

* Auf Github: https://github.com/yakamara/REDAXO_yrewrite/issues/
* im Forum: https://www.REDAXO.org/forum/
* im Slack-Channel: https://friendsofREDAXO.slack.com/

# Changelog

## Version 2.3 // 30.01.2017

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

## Version 2.2 // 19.09.2016

### Bugs
- Weiterleitungen funktionierten unter bestimmten Situationen nicht
- Änderungen an Domainnamen werden nun konsequent referenziert.


## Version 2.1 // 25.08.2016

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



## Version 2.0.1 // 11.02.2016

- Texte ergänzt / deutsch und englisch
- rex_yrewrite_scheme ergänzt um getSuffix() und aufruf von normalize() erlaubt und getScheme() ergänzt
- Fehlerinfo wenn noch keine Domain vorhanden ist.

### Bugs

- Caching in der htaccess angepasst. Ist zu unkontrolliert und allgemein. Deswegen erstmal draußen
- Workaround für RewriteBase, allgemeingültig gemacht



## Version 2.0 // 02.02.2016

- Portierung zu REDAXO 5
- Unterordner sind nun möglich
- http oder https, wird direkt in der Domainbezeichnung festgelegt
- Meta langhref ergänzt
- Fürs Verständnis aus "undefined" -> "default" gemacht.
- Standard-URL-Schema geändert .. aus .html -> /
- Canonical Urls ergänzt

