<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */

class rex_yrewrite_seo
{

    static $priority = array("1.0", "0.7", "0.5", "0.3", "0.1", "0.0");
    static $priority_default = "0.5";
    static $changefreq = array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never');
    static $changefreq_default = 'weekly';

    // TODO:
    /*
    public function getMeta($art, $field = 'title') {
        switch($field) {
            case("title"):
              return "title";
              break;

            case("description"):
              return "description";
              break;

          case("keywords"):
              return "keywords";
              break;
        }
        return '';
    }
    */

    public function sendRobotsTxt()
    {
        header("Content-Type: text/plain");
        // header content length ?
        $content = 'Sitemap: '.rex_yrewrite::getFullPath('sitemap.xml');
        $content .= "\n\n".'User-agent: *';
        $content .= "\n".'Disallow:';

        // TODO: weitere robots disallows rein.

        echo $content;
        exit;
    }

    public function sendSitemap()
    {
        global $REX;

        $sitemap = array();

        $domain = rex_yrewrite::getHost();
        if ( isset(rex_yrewrite::$paths['paths'][$domain]) ) {

            // var_dump(rex_yrewrite::$domainsByName[$domain]);
            // var_dump(rex_yrewrite::$paths['paths'][$domain]);

            foreach(rex_yrewrite::$paths['paths'][$domain] as $article_id => $path) {

                if( ($article = OOArticle::getArticleById($article_id)) ) {

                    $changefreq = $article->getValue('yrewrite_seochangefreq');
                    if(!in_array($changefreq,self::$changefreq)) {
                        $changefreq = self::$changefreq_default;
                    }

                    $priority = $article->getValue('yrewrite_seopriority');

                    if(!in_array($priority,self::$priority)) {
                        $priority = self::$priority_default;
                    }

                    $sitemap[] =
                        "\n".'<url>'.
                        "\n".'<loc>'.rex_yrewrite::getFullPath($path[0]).'</loc>'.
                        "\n".'<lastmod>'.date(DATE_W3C,$article->getValue('updatedate')).'</lastmod>'. // serverzeitzone passt
                        "\n".'<changefreq>'.$changefreq.'</changefreq>'.
                        "\n".'<priority>'.$priority.'</priority>'.
                        "\n".'</url>';

                }

            }

        }

        header('Content-Type: application/xml');
        $content = '<?xml version="1.0" encoding="UTF-8"?>';
        $content .= "\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $content .= implode("\n",$sitemap);
        $content .= "\n".'</urlset>';
        echo $content;
        exit;
    }

}