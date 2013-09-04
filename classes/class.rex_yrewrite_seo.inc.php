<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */

class rex_yrewrite_seo
{
    public
        $article = NULL,
        $domain = NULL;

    static
        $priority = array("1.0", "0.7", "0.5", "0.3", "0.1", "0.0"),
        $priority_default = "0.5",
        $changefreq = array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'),
        $changefreq_default = 'weekly',
        $robots_default = "User-agent: *\nDisallow",
        $title_scheme_default = '%YT / %T / %SN'
    ;

    public function rex_yrewrite_seo($article_id = 0)
    {
        global $REX;

        if($article_id == 0) {
          $article_id = $REX["ARTICLE_ID"];
        }

        if( ($article = OOArticle::getArticleById($article_id)) ) {
            $this->article = $article;
            $this->domain = rex_yrewrite::getDomainByArticleId($article_id);
        }

    }

    public function getMetaTags() {
        return
          '<title>'.$this->getTitle().'</title>'.
          "\n".'<meta name="description" content="'.$this->getDescription().'">'. //  lang="de"
          "\n".'<meta name="keywords" content="'.$this->getKeywords().'">'; //  lang="de"
    }

    public function getTitle()
    {
        global $REX;
        $title_scheme = trim(rex_yrewrite::$domainsByName[$this->domain]['title_scheme']);
        if($title_scheme == '') {
            $title_scheme = self::$title_scheme_default;
        }

        $ytitle = '';
        if($this->article && $this->article->getValue('yrewrite_title') != "") {
          $ytitle = $this->article->getValue('yrewrite_title');
        }

        $title = $title_scheme;
        $title = str_replace('%YT', $ytitle, $title);
        $title = str_replace('%T', $this->article->getValue('name'), $title);
        $title = str_replace('%SN', $REX['SERVERNAME'], $title);

        // TODO: ersetzungen noch überlegen - in welcher Form ?
        // %C = Kategoriename ?
        // %P = PATH ?

        return $title;
    }

    public function getDescription()
    {
        $description = rex_yrewrite::$domainsByName[$this->domain]['description'];
        if($this->article && $this->article->getValue('yrewrite_description') != "") {
            $description = $this->article->getValue('yrewrite_description');
        }
        return $description;
    }

    public function getKeywords()
    {
        $keywords = rex_yrewrite::$domainsByName[$this->domain]['keywords'];
        if($this->article && $this->article->getValue('yrewrite_keywords') != "") {
            $keywords = $this->article->getValue('yrewrite_keywords');
        }
        return $keywords;
    }

  // ----- global static functions

    public function sendRobotsTxt($domain = "")
    {
        if($domain == "") {
          $domain = rex_yrewrite::getHost();
        }

        header("Content-Type: text/plain");
        // header content length ?
        $content = 'Sitemap: '.rex_yrewrite::getFullPath('sitemap.xml');
        $content .= "\n\n".'User-agent: *';
        $content .= "\n".'Disallow:';

        if (isset(rex_yrewrite::$domainsByName[$domain])) {
            $robots = rex_yrewrite::$domainsByName[$domain]["robots"];
            if($robots != "") {
                $content .= "\n".$robots;
            }
        }

        echo $content;
        exit;
    }

    public function sendSitemap($domain = "")
    {
        global $REX;

        if($domain == "") {
            $domain = rex_yrewrite::getHost();
        }

        $sitemap = array();
        if ( isset(rex_yrewrite::$paths['paths'][$domain]) ) {

            // var_dump(rex_yrewrite::$domainsByName[$domain]);
            // var_dump(rex_yrewrite::$paths['paths'][$domain]);

            foreach(rex_yrewrite::$paths['paths'][$domain] as $article_id => $path) {

                if( ($article = OOArticle::getArticleById($article_id)) ) {

                    $changefreq = $article->getValue('yrewrite_changefreq');
                    if(!in_array($changefreq,self::$changefreq)) {
                        $changefreq = self::$changefreq_default;
                    }

                    $priority = $article->getValue('yrewrite_priority');

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