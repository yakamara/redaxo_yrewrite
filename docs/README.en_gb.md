# YRewrite

## Overview

This addon offers a way to run REDAXO with multiple domains. Multiple domains can be useful if

* multiple websites of a customer are managed in one installation,
* different languages (`clang`) of a website are accessible under different domains or subdomains,
* or both.

> Tip: In the first case, we recommend creating a category for each individual domain in the structure at the top level.

## Features

* Multiple domains manageable in one website
* Language dependencies of domains assignable
* SEO Features: Domain- and language-dependent robots and sitemap files
* Individual URL per article possible
* Page title scheme definable / per domain/language
* Alias domains pointing to the main domain
* General redirects. URLs to internal articles, files, external articles, even protocol exchange in e.g. `tel:`, `mailto:` u.a.
* Canonical Urls

## Installation

Prerequisite for the current version of YRewrite: REDAXO >= 5.5

* Install and activate via the REDAXO backend
* Run Setup

# Getting Started

## Setup

Under the tab `Setup` , the `sitemap.xml` and `robots.txt` can be viewed for each domain set up. You can also perform a setup that creates the Apache configuration for YRewrite from a `.htaccess`file.

### Apache Configuration for YRewrite

Run Setup to create a `.htaccess`file in the root directory that is required to use YRewrite. Subsequently, all front-end URLs are rewritten into search engine-friendly URLs ("rewriteing").

> **note** If the front-end URLs do not work after the setup is complete, please check if it is an Apache or NGINX server (see below). Also check if the web hosting package allows its own `.htaccess`rules.

> **Note:** The addon forwards all requests from `/media/` via the Media Manager add-on. Therefore, make sure that there is neither a structure category "Media", nor that none of your files for the frontend, e.CSS or JS files, are in it. Good places for this are the folders `/assets/` or using the theme add-on. If it is necessary to use a category called "Media", then [the corresponding lines in the .htaccess file must be commented out or renamed](https://github.com/yakamara/redaxo_yrewrite/blob/b519622a3be135f1380e35bf85783cc33e71664f/setup/.htaccess#L96-L97) and these must be used from now on when using media from the Media Manager. This has further implications, e.B. on protected files with YCom - commenting and renaming should therefore only be done by experienced REDAXO developers.

### NGINX Configuration for YRewrite

A complete nginx config for YRewrite.

> Note for PLESK websites: The directives can be stored in ***Settings for Apache & nginx*** the desired domain in the section ***Additional nginx instructions*** .

```nginx
charset utf-8;

location / {
  try_files $uri $uri/ /index.php$is_args$args;
}

rewrite ^/sitemap\.xml$ /index.php?rex_yrewrite_func=sitemap last;
rewrite ^/robots\.txt$ /index.php?rex_yrewrite_func=robots last;
rewrite ^/media[0-9]*/imagetypes/([^/]*)/([^/]*) /index.php?rex_media_type=$1&rex_media_file=$2&$args;
rewrite ^/media/([^/]*)/([^/]*) /index.php?rex_media_type=$1&rex_media_file=$2&$args;
rewrite ^/media/(.*) /index.php?rex_media_type=yrewrite_default&rex_media_file=$1&$query_string;
rewrite ^/images/([^/]*)/([^/]*) /index.php?rex_media_type=$1&rex_media_file=$2&$args;
rewrite ^/imagetypes/([^/]*)/([^/]*) /index.php?rex_media_type=$1&rex_media_file=$2;

// !!! IMPORTANT!!! If Let's Encrypt fails, comment out this line (but should work)
location ~ /\. { deny all; }

// Prohibit access to these directories
location ^~ /redaxo/src { deny all; }
location ^~ /redaxo/data { deny all; }
location ^~ /redaxo/cache { deny all; }
location ^~ /redaxo/bin { deny all; }


// In some cases, the following statement might be useful.

location ~ /\. (ttf|eot|woff|woff2)$ {
  add_header Access-Control-Allow-Origin *;
  expires 604800s;
}
```


## Add Domain

1. In YRewrite, under Domains, click the + sign.
2. Enter a domain, e.g. `https://www.meine-domain.de/`.
3. Select Mountpoint (optional). This is the starting article of a category in which YRewrite is to connect. All articles below the mount article can then be accessed via the domain. If no item is selected, all levels are assigned to this domain (default).
4. Select home page articles. This can be the mount article or a separate article page. This is called up as the start page of the domain.
5. Select error page items. This is the item that is issued with a 404 error code, e.B. if a page cannot be found or there is a typo in the address.
6. Language settings: Here you can select languages that are linked to the domain. For example, different domains can be implemented per language.
7. Enter the title scheme, e.g. `%T -`my domain. This title scheme can then be output in the website template.
8. Add robots.txt settings. See tip below.
9. Add a domain.

Repeat this procedure for all desired domains.

> **tip:** To reliably protect the installation during development against crawling bots and search engines, the `robots.txt` is not enough. There is also the `maintanance`addon from https://friendsofREDAXO.github.io

> **tip:** also store the domain in the Google Search Console and add the `sitemap.xml` there to speed up crawling. The domain should be stored in all four variations, i.e. with/without `https` and with/without `www.`. However, the `sitemap.xml` only in the main domain, preferably with `https://` and `www.`

> **Note:** Please enter domains with umlauts decoded. Conversion, e.g. with https://www.punycoder.com

## Add an alias domain

Alias domains are only needed if multiple domains point to the same folder in the server, but do not visit separate websites. e.B. `www.meinedomain.de` and `www.meine-domain.de`.

Alias domains do not have to be entered if the domain does not point to the server directory. Some hosters, for example, offer the possibility to redirect from `www.meinedomain.de` to `www.meine-domain.de` . Then the setting is not needed.

1. In "YRewrite" under "Domains" Click on the + sign
2. Enter an alias domain, e.g. `https://www.meine-domain.de/`
3. Select a target domain from YRewrite
4. Add an alias domain

## Redirects

Under Redirects, URLs can be defined, which are then redirected to a specific article or other address.

> **note:** This setting cannot redirect existing articles / URLs, but only URLs that do not exist in the REDAXO installation. This is the case, for example, with a relaunch, if old URLs are to be redirected to a new landing page.

> **tip**: This can also be used to change an article or category to a completely different URI protocol, e.g. `tel:`, `mailto:` u.a. These are also taken into account elsewhere, e.g. by the `rex_navigation::factory()`.

## Next steps

The `sitemap.xml` can be entered per domain, for example, in the Google Search Console to check the correct indexing of the domain(s) and their pages.

# Class Reference

## YRewrite Object

See also: https://github.com/yakamara/REDAXO_yrewrite/blob/master/lib/yrewrite.php

```
    $yrewrite = new rex_yrewrite;
    // dump($yrewrite); optionally display all properties and methods
```

**Methods**
```
```

## YRewrite domain object

See also: https://github.com/yakamara/REDAXO_yrewrite/blob/master/lib/domain.php

```
$domain = rex_yrewrite::getCurrentDomain(); dump($domain); optionally display all properties and methods
```

**Methods**
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
getFullUrlByArticleId($id, $clang = null, array $parameters = [], $separator = \'&\')
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
## YRewrite SEO Object

See also: https://github.com/yakamara/REDAXO_yrewrite/blob/master/lib/seo.php


```
$seo = new rex_yrewrite_seo(); dump($seo); optionally display all properties and methods
```

**Methods**

```
```

# Examples

## ID of the current domain in YRewrite

```
rex_yrewrite::getCurrentDomain()->getId();

```

Example return value: '1'

## mount ID of the domain

```
rex_yrewrite::getCurrentDomain()->getMountId();
```

Example return value: '5'


## Domain start item ID

```
rex_yrewrite::getCurrentDomain()->getStartId();
```

Example return value: '42'

## Error item ID of the domain

```
rex_yrewrite::getCurrentDomain()->getNotfoundId();
```

Example return value: '43'

## Name of the current domain

```
rex_yrewrite::getCurrentDomain()->getName();
```

Example return value: 'meine-domain.de'

## full URL of an item

```
rex_yrewrite::getFullUrlByArticleId(42);
```

Example return value: 'https://www.meine-domain.de/meine-kategorie/mein-artikel.html'

## To which domain does the current article belong?

```
rex_yrewrite::getDomainByArticleId(REX_ARTICLE_ID)->getName();
```

Example return value: 'meine-domain.de'

## Read meta tags ('description', 'title', etc.)

copy this code section into the '<head>' section of the template:

```php
$seo = new rex_yrewrite_seo();
echo $seo->getTags();
```

Output is:

```html
<meta name="description" content="Text entered in description field">
<meta name="robots" content="index, follow">
<link rel="canonical" href="https://example.org/de/" />
<link rel="alternate" hreflang="de" href="https://example.org/de/" />
<link rel="alternate" hreflang="en" href="https://example.org/en/" />
<meta property="og:title" content="Articlename / Websitetitle" />
<meta property="og:description" content="Text entered in description field" />
<meta property="og:image" content="https://example.org/media/yrewrite_seo_image/seo-image.jpg" />
<meta property="og:image:alt" content="Picture title from mediapool" />
<meta property="og:image:type" content="image/jpeg" />
<meta property="og:url" href="https://example.org/de/" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:title" content="Articlename / Websitetitle" />
<meta name="twitter:description" content="Text entered in description field" />
<meta name="twitter:url" content="https://example.org/de/" />
<meta name="twitter:image" content="https://example.org/media/yrewrite_seo_image/seo-image.jpg" />';
<meta name="twitter:image:alt" content="Picture title from mediapool" />
```

## Navigation Factory depending on the selected domain

Further information on the Navigation Factory of the REDAXO core in the API documentation under https://REDAXO.org/api/master/ and in the tricks of FriendsOfREDAXO: https://github.com/friendsofREDAXO/tricks/

```
$nav = rex_navigation::factory(); echo $nav->get(rex_yrewrite::getCurrentDomain()->getMountId(), 1, TRUE, TRUE);
```

## Output overview of all domains

```
$domains = array_filter(rex_sql::factory()->setDebug(0)->query(\'SELECT * FROM rex_yrewrite_domain\') foreach($domains as $domain) { dump($domain); }
```

# URL schemes for YRewrite

## Overview

YRewrite can be extended by schemes.

**Installation**

- Save as a file in the 'lib' folder of the __project AddOns__.
- File Name: 'eigene_rewrite_class.php'
- Insert into the 'boot.php' of the project add-on:

'''php
<?php
if (rex_addon::get(\'yrewrite\')->isAvailable()) {
    rex_yrewrite::setScheme(new eigene_rewrite_class());
}
```

Below we list a few examples.

## Set extension to .html

```php
<?php
class rex_yrewrite_scheme_mysuffix extends rex_yrewrite_scheme
{
    protected $suffix = \'.html\';
}
```

## Remove Trailing Slash

```php
<?php
class rex_yrewrite_scheme_mysuffix extends rex_yrewrite_scheme
{
    protected $suffix = null;
}
```

## URL Replacer

Replaces URLs of empty parent categories with the URLs of the next content-tagged (online) child category.

> Based on: https://gist.github.com/gharlan/a70704b1c309cb1281c1

### Forwarding regardless of whether content in starting articles of the parent category

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

### Forwarding only if there is no content in the starting article of the parent category

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

## Manipulate URL, here with the AddOn Sprog

For example, a placeholder such as {{contact}} can be used as the category name and replaced by the language variants stored in Sprog.

One Level, category name replacement by Sprog.

```php
<?php
class translate_url_with_sprog extends rex_yrewrite_scheme
{

    public function appendCategory($path, rex_category $cat, rex_yrewrite_domain $domain)
    {
        return $path;
    }

    public function appendArticle($path,  rex_article $art, rex_yrewrite_domain $domain)
    {
        return $path . \'/\' . $this->normalize(sprogdown($art->getName(), $art->getClangId()), $art->getClangId()) . \'/\';
    }
}
```

Multilevel, category name replacement by Sprog.

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

## Changing replacement patterns with your own schema

The replacement patterns can be changed with their own scheme. This example swaps `&` with `and` .

1. Create a file in the lib folder of the Project AddOn

```
<?php

class rex_project_rewrite_scheme extends rex_yrewrite_scheme
{
    /**
* @param string $string
     * @param int $clang
     *
     * @return string
     */
    public function normalize($string, $clang = 1)
    {
        $string = str_replace(
            ['&'],
            ['und'],
            $string
        );

        // Id 2 = Hungarian
        if ($clang == 2) {
            $string = str_replace(
                ['ő',  'ű'],
                ['oe', 'ue'],
                $string
            );
        }
        return parent::normalize($string, $clang);
    }
}
```

2. In the `boot.php`file of the `project`add-on, insert this code:

`rex_yrewrite::setScheme(new rex_project_rewrite_scheme());`

## Addons that bring their own schemes:

- YRewrite scheme: https://github.com/FriendsOfREDAXO/yrewrite_scheme


# Further support

* Report a bug via GitHub: https://github.com/yakamara/REDAXO_yrewrite/issues/
* Help via REDAXO Slack channel: https://friendsofREDAXO.slack.com/
* Tricks via FriendsOfREDAXO: https://friendsofredaxo.github.io/tricks/ at Addons > YRewrite
