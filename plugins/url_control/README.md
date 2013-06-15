redaxo_plugin_url_generate
================================================================================

Plugin zur URL-Generierung für eigene AddOns (ehemals Frau Schultze)


Beispiel: News AddOn
--------------------------------------------------------------------------------
Normlerweise wird eine News über eine Url wie **/news.html?news_id=1** geholt

Mit dem Plugin ist es möglich Urls wie **/news/news-title.html** zu erzeugen

Der Artikel **/news-title.html** selbst existiert dabei nicht. Es wird alles im Artikel **/news.html** abgehandelt

Um an die tatsächliche Id der einzelnen News zu kommen, wird folgende Methode verwendet:
```
$news_id = url_generate::getId();
```

Die Url holt man sich mit folgender Methode:
```
$news_url = url_generate::getUrlById($news_id, 'news_table');
```




Installation
--------------------------------------------------------------------------------
* Plugin in den plugin-Ordner des Rewriters laden
* Ordner **redaxo_plugin_url_generate** in **url_generate** umbenennen
* Plugin installieren und aktivieren


unterstützte Rewriter
--------------------------------------------------------------------------------
* [yrewriter](https://github.com/dergel/redaxo4_yrewrite) von dergel (Jan Kristinus)
* [rexseo](https://github.com/gn2netwerk/rexseo) von GN2 Netwerk und jdlx
* [rexseo42](https://github.com/rexdude/rexseo42) von RexDude
