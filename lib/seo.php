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

    public function getTitleTag()
    {
        return '<title>'.htmlspecialchars($this->getTitle()).'</title>'; //  lang="de"
    }

    public function getDescriptionTag()
    {
        return '<meta name="description" content="'.htmlspecialchars($this->getDescription()).'">'; //  lang="de"
    }

    public function getRobotsTag()
    {
        if ($this->article->getValue('yrewrite_index') == 1 || ($this->article->getValue('yrewrite_index') == 0 && $this->article->isOnline())) {
            return '<meta name="robots" content="index, follow">';
        } else {
            return '<meta name="robots" content="noindex, follow">';
        }
    }

    public function getTitle()
    {
        $title_scheme = htmlspecialchars_decode(trim($this->domain->getTitle()));
        if ($title_scheme == '') {
            $title_scheme = self::$title_scheme_default;
        }

        $ytitle = '';
        if ($this->article && $this->article->getValue('yrewrite_title') != '') {
            $ytitle = $this->article->getValue('yrewrite_title');
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
        $description = htmlspecialchars_decode(trim($this->domain->getDescription()));
        return $this->cleanString($description);
    }

    public function getHreflangTags()
    {
        $return = '';
        $current_mount_id = $this->domain->getMountId();

        $lang_domains = [];
        foreach(rex_yrewrite::getDomains() as $domain) {
            if($current_mount_id == $domain->getMountId()) {
                foreach($domain->getClangs() as $clang) {
                    if ( ($lang = rex_clang::get($clang)) ) {
                        $lang_domains[$lang->getCode()] = rex_yrewrite::getFullUrlByArticleId($domain->getStartId(),$lang->getId());
                    }
                }
            }
        }

        foreach($lang_domains as $code => $url){
            $return .= '<link rel="alternate" hreflang="'.$code.'" href="'.$url.'" />';
        }

        return $return;
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
        if ($domain == '') {
            $domain = rex_yrewrite::getHost();
        }

        $sitemap = [];
        if (rex_yrewrite::getDomainByName($domain)) {
            $domain = rex_yrewrite::getDomainByName($domain);

            $domain_article_id = $domain->getStartId();
            $paths = 0;
            if (($dai = rex_article::get($domain_article_id))) {
                $paths = count($dai->getParentTree());
            }

            foreach (rex_yrewrite::getPathsByDomain($domain->getName()) as $article_id => $path) {
                foreach ($domain->getClangs() as $clang_id) {
                    if (
                        ($article = rex_article::get($article_id, $clang_id)) &&
                        self::checkArticlePerm($article) &&
                        ($article->getValue('yrewrite_index') == 1 || ($article->isOnline() && $article->getValue('yrewrite_index') == 0))

                    ) {
                        $changefreq = $article->getValue('yrewrite_changefreq');
                        if (!in_array($changefreq, self::$changefreq)) {
                            $changefreq = self::$changefreq_default;
                        }

                        $priority = $article->getValue('yrewrite_priority');

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

        header('Content-Type: application/xml');
        $content = '<?xml version="1.0" encoding="UTF-8"?>';
        $content .= "\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $content .= implode("\n", $sitemap);
        $content .= "\n".'</urlset>';
        echo $content;
        exit;
    }

    public static function checkArticlePerm($article)
    {
        $perm = true;
        if (class_exists('rex_com_auth')) {
            $perm = rex_com_auth::checkPerm($article);
            if ($perm == false) {
                return false;
            }
        }
        $perm = rex_extension::registerPoint(new rex_extension_point('YREWRITE_ARTICLE_PERM', $perm, ['article' => $article]));
        return $perm;
    }
}
