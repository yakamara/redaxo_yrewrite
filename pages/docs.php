<?php

/**
 * YREWRITE Addon.
 *
 * @author alexplusde, Thomas Skerbis
 *
 * @package redaxo\yrewrite
 *
 * @var rex_addon $this
 */

$content = '
## Anwendungsfall

### Erklärung

Dieses Addon bietet eine Möglichkeit, Redaxo mit mehreren Domains zu betreiben. Mehrere Domains können dann sinnvoll sein, wenn

* mehrere Websites eines Kunden in einer Installation verwaltet werden,
* verschiedene Sprachen (`clang`) einer Website unter unterschiedlichen Domains oder Subdomains erreichbar sind,
* oder beides.

> Tipp: Wir empfehlen im ersten Fall, für jede einzelne Domain in der Struktur auf der obersten Ebene eine Kategorie anzulegen.

## Erste Schritte

### Installation

Voraussetzung für die aktuelle Version von YRewrite: REDAXO 5.5

Nach dem Installieren und Aktivieren des Addons unter `YRewrite > Setup` die `.htaccess`-Datei von REDAXO erstellen lassen. 

Anschließend können ein oder mehrere Domains zu YRewrite hinzugefügt werden, dabei werden jeweils auch eine virtuelle `robots.txt` und `sitemap.xml` erstellt.

### Domain hinzufügen

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

> Tipp: Um die Installation während der Entwicklung zuverlässig gegen ein Crawling von Bots und Suchmaschinen zu schützen, genügt die `robots.txt` nicht. Dazu gibt es das `maintanance`-Addon von https://friendsofredaxo.github.io

> Tipp: Die Domain auch in der Google Search Console hinterlegen und die `sitemap.xml` dort hinzufügen, um das Crawling zu beschleunigen. Die Domain sollte in allen vier Variationen hinterlegt werden, also mit/ohne `https` und mit/ohne `www.`. Die `sitemap.xml` jedoch nur in der Hauptdomain, am besten mit `https://` und `www.`

> Hinweis: Domains mit Umlauten bitte derzeit decodiert eintragen. Umwandlung bspw. mit https://www.punycoder.com

### Alias-Domain hinzufügen

Alias-Domains werden nur dann benötigt, wenn mehrere Domains auf den selben Ordner im Server zeigen, aber keine separaten Websites aufrufen. z.B. `www.meinedomain.de` und `www.meine-domain.de`.

Alias-Domains müssen nicht eingetragen werden, wenn die Domain nicht auf das Serververzeichnis zeigt. Einige Hoster bieten bspw. von sich aus die Möglichkeit, per Redirect von `www.meinedomain.de` auf `www.meine-domain.de` weiterzuleiten. Dann wird die Einstellung nicht benötigt.

1. In "YRewrite" unter "Domains" Auf das +-Zeichen klicken
2. Alias-Domain eintragen, bspw. `https://www.meine-domain.de/`
3. Ziel-Domain aus YRewrite auswählen
4. Alias-Domain hinzufügen

### Weiterleitungen

Unter Weiterleitungen können URLs definiert werden, die dann auf einen bestimmten Artikel oder eine andere Adresse umgeleitet werden.

> **Hinweis:** Mit dieser Einstellung können nicht bereits vorhandene Artikel / URLs umgeleitet werden, sondern nur URLs, die in der REDAXO-Installation nicht vorhanden sind. Das ist bspw. bei einem Relaunch der Fall, wenn alte URLs auf eine neue Zielseite umgeleitet werden sollen.

### Setup

Unter `Setup` kann die `.htaccess`-Datei neu überschrieben werden, die für die Verwendung von YRewrite benötigt wird. Außerdem sind die `sitemap.xml` und `robots.txt` je Domain einsehbar.


## Klassen-Referenz

### YRewrite-Objekt
Siehe auch: https://github.com/yakamara/redaxo_yrewrite/blob/master/lib/yrewrite.php

```
$yrewrite = new rex_yrewrite;
# dump($yrewrite); // optional alle Eigenschaften und Methoden anzeigen
```

**Methoden**

```
```

### YRewrite-Domain-Objekt

Siehe auch: https://github.com/yakamara/redaxo_yrewrite/blob/master/lib/domain.php

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
### YRewrite-SEO-Objekt

Siehe auch: https://github.com/yakamara/redaxo_yrewrite/blob/master/lib/seo.php


```
$seo = new rex_yrewrite_seo();
dump($seo); // optional alle Eigenschaften und Methoden anzeigen
```

**Methoden**

```
```

## Beispiele

### ID der aktuellen Domain in YRewrite

```
rex_yrewrite::getCurrentDomain()->getId();

```

Beispiel-Rückgabewert: `1`

### Mount-ID der Domain

```
rex_yrewrite::getCurrentDomain()->getMountId();
```

Beispiel-Rückgabewert: `5`


### Startartikel-ID der Domain

```
rex_yrewrite::getCurrentDomain()->getStartArticleId();
```

Beispiel-Rückgabewert: `42`

### Fehler-Artikel-ID der Domain

```
rex_yrewrite::getCurrentDomain()->getNotfoundId();
```

Beispiel-Rückgabewert: `43`

### Name der aktuellen Domain

```
rex_yrewrite::getCurrentDomain()->getName();
```

Beispiel-Rückgabewert: `meine-domain.de`

### vollständige URL eines Artikels

```
rex_yrewrite::getFullUrlByArticleId(42);
```

Beispiel-Rückgabewert: `https://www.meine-domain.de/meine-kategorie/mein-artikel.html`

### Zu welcher Domain gehört der aktuelle Artikel?

```
rex_yrewrite::getDomainByArticleId(REX_ARTICLE_ID)->getName();
```

Beispiel-Rückgabewert: `meine-domain.de`

### Meta-Tags auslesen (`description`, `title`, usw.)

Diesen Codeabschnitt in den `<head>`-Bereich des Templates kopieren:

```
$seo = new rex_yrewrite_seo();
echo $seo->getTitleTag();
echo $seo->getDescriptionTag();
echo $seo->getRobotsTag();
echo $seo->getHreflangTags();
echo $seo->getCanonicalUrlTag();
```

### Navigation Factory in Abhängigkeit der gewählten Domain

Weitere Informaionen zur Navigation Factory des REDAXO-Cores in der API-Dokumentation unter https://redaxo.org/api/master/ und bei den Tricks von FriendsOfREDAXO: https://github.com/friendsofredaxo/tricks/

```
$nav = rex_navigation::factory();
echo $nav->get(rex_yrewrite::getCurrentDomain()->getMountId(), 1, TRUE, TRUE);
```

### Übersicht aller Domains ausgeben

```
$domains = array_filter(rex_sql::factory()->setDebug(0)->query(\'SELECT * FROM rex_yrewrite_domain\')
foreach($domains as $domain) {
    dump($domain);
}
```

## URL-Schemes für YRewrite

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

### Endung auf .html setzen

```php
<?php
class rex_yrewrite_scheme_mysuffix extends rex_yrewrite_scheme
{
    protected $suffix = \'.html\';
}
```

### Trailing Slash entfernen

```php
<?php
class rex_yrewrite_scheme_mysuffix extends rex_yrewrite_scheme
{
    protected $suffix = Null;
}
```

### URL-Replacer

Ersetzt URLs leerer Elternkategorien mit den URLs der nächsten mit inhalt versehenen (online-)Kindkategorie.

> Basiert auf: https://gist.github.com/gharlan/a70704b1c309cb1281c1

#### Weiterleitung egal ob Inhalt in Startartikel der Elternkategorie

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

#### Weiterleitung nur wenn kein Inhalt im Startartikel der Elternkategorie

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

### URL manipulieren, hier mit dem AddOn Sprog

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
        return $path . \'/\' . $this->normalize(sprogdown($art->getName(), $art->getClang()), $art->getClang()) . \'/\';
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
        return $path . \'/\' . $this->normalize(sprogdown($cat->getName(), $cat->getClang()), $cat->getClang());
    }
}
```

### Addons, die eigene Schemes mitbringen:

- YRewrite scheme: https://github.com/FriendsOfREDAXO/yrewrite_scheme
- xcore: https://github.com/RexDude/xcore


## Links und Hilfe

### Bugmeldungen Hilfe und Links

* Auf Github: https://github.com/yakamara/redaxo_yrewrite/issues/
* im Forum: https://www.redaxo.org/forum/
* im Slack-Channel: https://friendsofredaxo.slack.com/';

$content_blocks = [];

$h2_blocks = explode("\n## ", $content);

foreach ($h2_blocks as $h2_i => $h2_block) {
    preg_match('/(.*)\n^(?:.|\n(?!#))*/m', $h2_block, $headline);

    if (isset($headline[1])) {
        $navi_list[] = '* '.$headline[1];
        $content_h2_block = '# '.$headline[0];

        preg_match_all('/(?!### )*^### (.*)\n((?:.|\n(?!### ))*)/m', $h2_block, $matches);

        if (count($matches[0]) > 0) {
            $navi_elememts = $matches[1];
            $blocks = $matches[2];
            foreach ($navi_elememts as $h3_i => $navi_elememt) {
                $navi_list[] = '	* <a href="index.php?page=yrewrite/docs&amp;n='.$h2_i.'-'.$h3_i.'">'.$navi_elememt.'</a>';
                $content_blocks[$h2_i.'-'.$h3_i] = $content_h2_block."\n## ".$navi_elememt.$blocks[$h3_i];
            }
        }
    }
}

reset($content_blocks);
$n = rex_request('n', 'string', key($content_blocks));
if (!isset($content_blocks[$n])) {
    $n = key($content_blocks);
}

$navi_view = implode("\n", $navi_list);
$blocks_view = $content_blocks[$n];

$miu = rex_markdown::factory();
$blocks_view = $miu->parse($blocks_view);
$navi_view = $miu->parse($navi_view);

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('yrewrite_docs_navigation').'', false);
$fragment->setVar('body', $navi_view, false);
$navi = $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('docs').' [ <a href="https://github.com/yakamara/redaxo_yrewrite/blob/master/pages/docs.php">docs.php</a> ]', false);
$fragment->setVar('body', $blocks_view, false);
$content = $fragment->parse('core/page/section.php');

echo '<section class="rex-yform-docs">
    <div class="row">
    <div class="col-md-4 yform-docs-navi">'.$navi.'</div>
    <div class="col-md-8 yform-docs-content">'.$content.'</div>
    </div>
</section>';
