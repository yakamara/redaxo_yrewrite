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
        $title_scheme_default = '%T / %SN';

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

    public function getTitleTag() {
        return '<title>'.htmlspecialchars($this->getTitle()).'</title>'; //  lang="de"
    }

    public function getDescriptionTag() {
        return '<meta name="description" content="'.htmlspecialchars($this->getDescription()).'">'; //  lang="de"
    }

    public function getTitle()
    {
        global $REX;
        $title_scheme = htmlspecialchars_decode(trim(rex_yrewrite::$domainsByName[$this->domain]['title_scheme']));
        if($title_scheme == '') {
            $title_scheme = self::$title_scheme_default;
        }

        $ytitle = '';
        if($this->article && $this->article->getValue('yrewrite_title') != "") {
          $ytitle = $this->article->getValue('yrewrite_title');
        }
        if($ytitle == '') {
          $ytitle = $this->article->getValue('name');
        }

        $title = $title_scheme;
        $title = str_replace('%T', $ytitle, $title);
        $title = str_replace('%SN', $REX['SERVERNAME'], $title);

        return $this->cleanString($title);
    }

    public function getDescription()
    {
        $description = htmlspecialchars_decode(trim(rex_yrewrite::$domainsByName[$this->domain]['description']));
        if($this->article && $this->article->getValue('yrewrite_description') != "") {
            $description = $this->article->getValue('yrewrite_description');
        }
        return $this->cleanString($description);
    }

    public function cleanString($str) {
        return str_replace(array("\n","\r"),array(' ',''), $str);

    }


    // ----- global static functions

    public function sendRobotsTxt($domain = "")
    {
        if($domain == "") {
          $domain = rex_yrewrite::getHost();
        }

        header("Content-Type: text/plain");
        // header content length ?
        $content = 'Sitemap: '.rex_yrewrite::getFullPath('sitemap.xml')."\n\n";


        if (isset(rex_yrewrite::$domainsByName[$domain])) {
            $robots = rex_yrewrite::$domainsByName[$domain]["robots"];
            if($robots != "") {
                $content .= $robots;
            } else {
                $content .= self::$robots_default;
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

                if( ($article = OOArticle::getArticleById($article_id)) && $article->isOnline() && self::getArticlePerm($article)) {

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

    static function getArticlePerm($article) {
        $perm = true;
        if ( method_exists("rex_com_auth", "checkPerm") ) {
          $perm = rex_com_auth::checkPerm($article);
        }
        $perm = rex_register_extension_point('YREWRITE_ARTICLE_PERM', $perm, array( 'article' => $article));
        return $perm;
    }

}