<?php

/**
 * YREWRITE Addon.
 *
 * @author jan.kristinus@yakamara.de
 *
 * @package redaxo\yrewrite
 */

class rex_yrewrite_seo
{
    public $article = null,
        $domain = null;

    public static $priority = ['1.0', '0.7', '0.5', '0.3', '0.1', '0.0'],
        $priority_default = '',
        $index_setting_default = 0,
        $changefreq = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'],
        $changefreq_default = 'weekly',
        $robots_default = "User-agent: *\nDisallow:",
        $title_scheme_default = '%T / %SN';

    /**
     * @var string
     */
    public static $meta_title_field = 'yrewrite_title';
    /**
     * @var string
     */
    public static $meta_description_field = 'yrewrite_description';
    /**
     * @var string
     */
    public static $meta_changefreq_field = 'yrewrite_changefreq';
    /**
     * @var string
     */
    public static $meta_priority_field = 'yrewrite_priority';
    /**
     * @var string
     */
    public static $meta_index_field = 'yrewrite_index';
    /**
     * @var string
     */
    public static $meta_canonical_url_field = 'yrewrite_canonical_url';

    public function __construct($article_id = 0, $clang = null)
    {
        if ($article_id == 0) {
            $article_id = rex_article::getCurrentId();
        }
        if (is_null($clang)) {
            $clang = rex_clang::getCurrentId();
        }

        if (($article = rex_article::get($article_id, $clang))) {
            $this->article = $article;
            $this->domain = rex_yrewrite::getDomainByArticleId($article_id, $clang);
        }
    }

    public function getTags(): string
    {
        $tags = [];
        $tagsOg = [];
        $tagsTwitter = [];
        $tagsTwitter['twitter:card'] = '<meta name="twitter:card" content="summary" />';

        $title = rex_escape(strip_tags($this->getTitle()));
        $tags['title'] = '<title>'.$title.'</title>';
        $tagsOg['og:title'] = '<meta property="og:title" content="'.$title.'" />';
        $tagsTwitter['twitter:title'] = '<meta name="twitter:title" content="'.$title.'" />';

        $description = rex_escape(strip_tags($this->getDescription()));
        if ($description != '') {
            $tags['description'] = '<meta name="description" content="'.$description.'">';
            $tagsOg['og:description'] = '<meta property="og:description" content="'.$description.'" />';
            $tagsTwitter['twitter:description'] = '<meta name="twitter:description" content="'.$description.'" />';
        }

        $content = 'noindex, nofollow';
        if ($this->article->getValue(self::$meta_index_field) == 1 || ($this->article->getValue(self::$meta_index_field) == 0 && $this->article->isOnline())) {
            $content = 'index, follow';
        }
        $tags['robots'] = '<meta name="robots" content="'.$content.'">';

        $canonicalUrl = rex_escape($this->getCanonicalUrl());
        $tags['canonical'] = '<link rel="canonical" href="'.$canonicalUrl.'" />';
        $tagsOg['og:url'] = '<meta property="og:url" href="'.$canonicalUrl.'" />';
        $tagsTwitter['twitter:url'] = '<meta name="twitter:url" content="'.$canonicalUrl.'" />';


        $hrefs = $this->getHrefLangs();
        foreach ($hrefs as $code => $url){
            $tags['hreflang:'.$code] = '<link rel="alternate" hreflang="' . $code . '" href="' . $url . '" />';
        }

        $tags += $tagsOg + $tagsTwitter;
        $tags = \rex_extension::registerPoint(new \rex_extension_point('YREWRITE_SEO_TAGS', $tags));
        return implode("\n", $tags);
    }

    /** @deprecated use getTags instead */
    public function getTitleTag()
    {
        return '<title>'.rex_escape(strip_tags($this->getTitle())).'</title>'; // lang="de"
    }

    /** @deprecated use getTags instead */
    public function getDescriptionTag()
    {
        return '<meta name="description" content="'.rex_escape(strip_tags($this->getDescription())).'">'; //  lang="de"
    }

    /** @deprecated use getTags instead */
    public function getCanonicalUrlTag()
    {
        return '<link rel="canonical" href="'.rex_escape($this->getCanonicalUrl()).'" />';
    }

    /** @deprecated use getTags instead */
    public function getRobotsTag()
    {
        if ($this->article->getValue(self::$meta_index_field) == 1 || ($this->article->getValue(self::$meta_index_field) == 0 && $this->article->isOnline())) {
            return '<meta name="robots" content="index, follow">';
        } else {
            return '<meta name="robots" content="noindex, nofollow">';
        }
    }

    /** @deprecated use getTags instead */
    public function getHreflangTags()
    {
        $return = '';
        $lang_domains = $this->getHrefLangs();

        foreach ($lang_domains as $code => $url){
            $return .= '<link rel="alternate" hreflang="' . $code . '" href="' . $url . '" />';
        }
        return $return;
    }


    public function getTitle()
    {
        $title_scheme = htmlspecialchars_decode(trim($this->domain->getTitle()));
        if ($title_scheme == '') {
            $title_scheme = self::$title_scheme_default;
        }

        $ytitle = '';
        if ($this->article && $this->article->getValue(self::$meta_title_field) != '') {
            $ytitle = $this->article->getValue(self::$meta_title_field);
        }
        if ($ytitle == '') {
            $ytitle = $this->article->getValue('name');
        }

        $title = $title_scheme;
        $title = str_replace('%T', $ytitle, $title);
        $title = str_replace('%SN', rex::getServerName(), $title);

        return $this->cleanString($title);
    }

    public function getDescription()
    {
        return $this->cleanString($this->article->getValue(self::$meta_description_field));
    }

    public function getCanonicalUrl()
    {
        $canonical_url = trim($this->article->getValue(self::$meta_canonical_url_field));
        if ($canonical_url == "") {
            $canonical_url = rex_yrewrite::getFullUrlByArticleId($this->article->getId(), $this->article->getClangId());
        }
        $canonical_url = rex_extension::registerPoint(new rex_extension_point('YREWRITE_CANONICAL_URL', $canonical_url, ['article' => $this->article]));
        return $canonical_url;
    }

    public function getHrefLangs()
    {
        $current_mount_id = $this->domain->getMountId();

        $lang_domains = [];
        foreach (rex_yrewrite::getDomains() as $domain) {
            if ($current_mount_id == $domain->getMountId()) {
                foreach ($domain->getClangs() as $clang) {
                    if ($lang = rex_clang::get($clang)) {
                        $article = rex_article::getCurrent($clang);
                        if ($article->isOnline() && $lang->isOnline())
                        {
                            $lang_domains[$lang->getCode()] = rex_yrewrite::getFullUrlByArticleId($article->getId(), $lang->getId());
                        }
                    }
                }
                break;
            }
        }

        return rex_extension::registerPoint(new rex_extension_point('YREWRITE_HREFLANG_TAGS', $lang_domains, ['article' => $this->article]));
    }

    public function cleanString($str)
    {
        return str_replace(["\n","\r"], [' ',''], $str);
    }

    // ----- global static functions

    public function sendRobotsTxt($domain = '')
    {
        if ($domain == '') {
            $domain = rex_yrewrite::getHost();
        }

        header('Content-Type: text/plain');
        // header content length ?
        $content = 'Sitemap: '.rex_yrewrite::getFullPath('sitemap.xml')."\n\n";

        if (rex_yrewrite::getDomainByName($domain)) {
            $robots = rex_yrewrite::getDomainByName($domain)->getRobots();
            if ($robots != '') {
                $content .= $robots;
            } else {
                $content .= self::$robots_default;
            }
        }

        echo $content;
        exit;
    }

    public function sendSitemap($domain = '')
    {

        $domains = rex_yrewrite::getDomains();

        if ($domain == '') {
            $domain = rex_yrewrite::getHost();
        }

        $sitemap = [];

        if (rex_yrewrite::getDomainByName($domain) || count($domains) == 1 ) {

            if (count($domains) == 1) {
                $domain = rex_yrewrite::getDefaultDomain();

            } else {
                $domain = rex_yrewrite::getDomainByName($domain);

            }

            $domain_article_id = $domain->getStartId();
            $paths = 0;
            if (($dai = rex_article::get($domain_article_id))) {
                $paths = count($dai->getParentTree());
            }

            foreach (rex_yrewrite::getPathsByDomain($domain->getName()) as $article_id => $path) {
                foreach ($domain->getClangs() as $clang_id) {

                    if (!rex_clang::get($clang_id)->isOnline()) {
                        continue;
                    }

                    $article = rex_article::get($article_id, $clang_id);

                    if (
                        ($article) &&
                        $article->isPermitted() &&
                        ($article->getValue(self::$meta_index_field) == 1 || ($article->isOnline() && $article->getValue(self::$meta_index_field) == 0)) &&
                        ($article_id != $domain->getNotfoundId() || $article_id == $domain->getStartId())

                    ) {

                        $changefreq = $article->getValue(self::$meta_changefreq_field);
                        if (!in_array($changefreq, self::$changefreq)) {
                            $changefreq = self::$changefreq_default;
                        }

                        $priority = $article->getValue(self::$meta_priority_field);

                        if (!in_array($priority, self::$priority)) {
                            $article_paths = count($article->getParentTree());
                            $prio = $article_paths - $paths - 1;
                            if ($prio < 0) {
                                $prio = 0;
                            }

                            if (isset(self::$priority[$prio])) {
                                $priority = self::$priority[$prio];
                            } else {
                                $priority = self::$priority_default;
                            }
                        }

                        $sitemap[] =
                          "\n".'<url>'.
                          "\n".'<loc>'.rex_yrewrite::getFullPath($path[$clang_id]).'</loc>'.
                          "\n".'<lastmod>'.date(DATE_W3C, $article->getValue('updatedate')).'</lastmod>'. // serverzeitzone passt
                          "\n".'<changefreq>'.$changefreq.'</changefreq>'.
                          "\n".'<priority>'.$priority.'</priority>'.
                          "\n".'</url>';
                    }
                }
            }
            $sitemap = rex_extension::registerPoint(new rex_extension_point('YREWRITE_DOMAIN_SITEMAP', $sitemap, ['domain' => $domain]));
        }
        $sitemap = rex_extension::registerPoint(new rex_extension_point('YREWRITE_SITEMAP', $sitemap));

        rex_response::cleanOutputBuffers();
        header('Content-Type: application/xml');
        $content = '<?xml version="1.0" encoding="UTF-8"?>';
        $content .= "\n".'<?xml-stylesheet type="text/xsl" href="assets/addons/yrewrite/xsl-stylesheets/xml-sitemap.xsl"?>';
        $content .= "\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
        $content .= implode("\n", $sitemap);
        $content .= "\n".'</urlset>';
        echo $content;
        exit;
    }

    /* @deprecated */
    public static function checkArticlePerm($article)
    {
        return $article->isPermitted();
    }
}
