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
    public $article = NULL,
        $domain = NULL;

    public static $priority = ['1.0', '0.7', '0.5', '0.3', '0.1', '0.0'],
        $priority_default = '',
        $index_setting_default = 0,
        $changefreq = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'],
        $changefreq_default = 'weekly',
        $robots_default = "User-agent: *\nDisallow:",
        $title_scheme_default = '%T / %SN';

    public function __construct($article_id = 0, $clang = NULL)
    {
        if ($article_id == 0)
        {
            $article_id = rex_article::getCurrentId();
        }
        if (is_null($clang))
        {
            $clang = rex_clang::getCurrentId();
        }

        if (($article = rex_article::get($article_id, $clang)))
        {
            $this->article = $article;
            $this->domain  = rex_yrewrite::getDomainByArticleId($article_id, $clang);
        }
    }

    public function getTitleTag()
    {
        return '<title>' . htmlspecialchars($this->getTitle()) . '</title>'; //  lang="de"
    }

    public function getDescriptionTag()
    {
        return '<meta name="description" content="' . htmlspecialchars($this->getDescription()) . '">'; //  lang="de"
    }

    public function getCanonicalUrlTag()
    {
        $canonical_url = trim($this->article->getValue('yrewrite_canonical_url'));
        if ($canonical_url == "")
        {
            $canonical_url = rex_yrewrite::getFullUrlByArticleId($this->article->getId(), $this->article->getClang());
        }
        $canonical_url = rex_extension::registerPoint(new rex_extension_point('YREWRITE_CANONICAL_URL', $canonical_url));
        return $canonical_url ? '<link rel="canonical" href="' . htmlspecialchars($canonical_url) . '" />' : '';
    }

    public function getRobotsTag()
    {
        if ($this->article->getValue('yrewrite_index') == 1 || ($this->article->getValue('yrewrite_index') == 0 && $this->article->isOnline()))
        {
            return '<meta name="robots" content="index, follow">';
        }
        else
        {
            return '<meta name="robots" content="noindex, follow">';
        }
    }

    public function getTitle()
    {
        $title_scheme = htmlspecialchars_decode(trim($this->domain->getTitle()));
        if ($title_scheme == '')
        {
            $title_scheme = self::$title_scheme_default;
        }

        $ytitle = '';
        if ($this->article && $this->article->getValue('yrewrite_title') != '')
        {
            $ytitle = $this->article->getValue('yrewrite_title');
        }
        if ($ytitle == '')
        {
            $ytitle = $this->article->getValue('name');
        }

        $title = $title_scheme;
        $title = str_replace('%T', $ytitle, $title);
        $title = str_replace('%SN', rex::getServerName(), $title);
        $title = rex_extension::registerPoint(new rex_extension_point('YREWRITE_TITLE', $title, ['scheme' => $title_scheme, 'sitename' => rex::getServerName(), 'title' => $ytitle]));

        return $this->cleanString($title);
    }

    public function getDescription($content_length = 160)
    {
        $description = $this->article->getValue('yrewrite_description');
        $description = rex_extension::registerPoint(new rex_extension_point('YREWRITE_DESCRIPTION', $description));

        if (trim($description) <> '')
        {
            $description = strip_tags($description);
            $description = wordwrap($description, $content_length, '' . "|||||||");
            $description = explode("|||||||", $description);
            $description = array_shift($description);
        }
        return $this->cleanString($description);
    }

    public function getHreflangTags()
    {
        $return           = '';
        $current_mount_id = $this->domain->getMountId();

        $lang_domains = [];
        foreach (rex_yrewrite::getDomains() as $domain)
        {
            if ($current_mount_id == $domain->getMountId())
            {
                foreach ($domain->getClangs() as $clang)
                {
                    if ($lang = rex_clang::get($clang))
                    {
                        $article = rex_article::getCurrent($clang);
                        if ($article->isOnline())
                        {
                            $lang_domains[$lang->getCode()] = rex_yrewrite::getFullUrlByArticleId($article->getId(), $lang->getId());
                        }
                    }
                }
                break;
            }
        }
        $x_default    = rex_config::get('project', 'default-lang-code', rex_clang::get(rex_clang::getStartId())->getCode());
        $lang_domains = rex_extension::registerPoint(new rex_extension_point('YREWRITE_HREFLANG_TAGS', $lang_domains));

        foreach ($lang_domains as $code => $url)
        {
            $return .= '<link rel="alternate" hreflang="' . ($code == $x_default ? 'x-default' : $code) . '" href="' . $url . '" />';
        }
        return $return;
    }

    public function getSocialsTags()
    {
        return '
            <meta property="og:url" content="' . strtr(rex_yrewrite::getFullPath(rex_getUrl()), ['//' => '/'])  . '"/>
            <meta property="og:title" content="' . $this->getTitle() . '"/>
            <meta property="og:description" content="' . $this->getDescription(200) . '"/>
            <meta property="og:type" content="Article"/>
        ';
    }


    public function getImage()
    {
        return rex_extension::registerPoint(new rex_extension_point('YREWRITE_IMAGE', ''));
    }

    public function getImageTags()
    {
        $content = '';
        $media   = $this->getImage();

        if ($media)
        {
            $name = $media->getValue('name');
            $data = rex_extension::registerPoint(new rex_extension_point('YREWRITE_IMAGE_DATA', [
                'src'    => strtr(rex_yrewrite::getFullPath(\rex_url::media($name)), ['//' => '/']),
                'width'  => $media->getValue('width'),
                'height' => $media->getValue('height'),
            ], [
                'image' => $name,
            ]));

            $content = '
                <meta property="og:image" content="' . $data['src'] . '" />
                <meta property="og:image:width" content="' . $data['width'] . '" />
                <meta property="og:image:height" content="' . $data['height'] . '" />
                <meta name="image" content="' . $data['src'] . '" />
            ';
        }
        return $content;
    }


    public function cleanString($str)
    {
        return str_replace(["\n", "\r"], [' ', ''], $str);
    }

    // ----- global static functions

    public function sendRobotsTxt($domain = '')
    {
        if ($domain == '')
        {
            $domain = rex_yrewrite::getHost();
        }

        header('Content-Type: text/plain');
        // header content length ?
        $content = 'Sitemap: ' . rex_yrewrite::getFullPath('sitemap.xml') . "\n\n";

        if (rex_yrewrite::getDomainByName($domain))
        {
            $robots = rex_yrewrite::getDomainByName($domain)->getRobots();
            if ($robots != '')
            {
                $content .= $robots;
            }
            else
            {
                $content .= self::$robots_default;
            }
        }

        echo $content;
        exit;
    }

    public function sendSitemap($domain = '')
    {

        $domains = rex_yrewrite::getDomains();

        if ($domain == '')
        {
            $domain = rex_yrewrite::getHost();
        }

        $sitemap = [];

        if (rex_yrewrite::getDomainByName($domain) || count($domains) == 1)
        {

            if (count($domains) == 1)
            {
                $domain = rex_yrewrite::getDefaultDomain();
            }
            else
            {
                $domain = rex_yrewrite::getDomainByName($domain);
            }

            $domain_article_id = $domain->getStartId();
            $paths             = 0;
            if (($dai = rex_article::get($domain_article_id)))
            {
                $paths = count($dai->getParentTree());
            }

            foreach (rex_yrewrite::getPathsByDomain($domain->getName()) as $article_id => $path)
            {
                foreach ($domain->getClangs() as $clang_id)
                {
                    if (!rex_clang::get($clang_id)->isOnline()) {
                        continue;
                    }

                    $article = rex_article::get($article_id, $clang_id);

                    if (
                        ($article) &&
                        self::checkArticlePerm($article) &&
                        ($article->getValue('yrewrite_index') == 1 || ($article->isOnline() && $article->getValue('yrewrite_index') == 0)) &&
                        ($article_id != $domain->getNotfoundId() || $article_id == $domain->getStartId())

                    )
                    {

                        $changefreq = $article->getValue('yrewrite_changefreq');
                        if (!in_array($changefreq, self::$changefreq))
                        {
                            $changefreq = self::$changefreq_default;
                        }

                        $priority = $article->getValue('yrewrite_priority');

                        if (!in_array($priority, self::$priority))
                        {
                            $article_paths = count($article->getParentTree());
                            $prio          = $article_paths - $paths - 1;
                            if ($prio < 0)
                            {
                                $prio = 0;
                            }

                            if (isset(self::$priority[$prio]))
                            {
                                $priority = self::$priority[$prio];
                            }
                            else
                            {
                                $priority = self::$priority_default;
                            }
                        }

                        $sitemap[] =
                            "\n" . '<url>' .
                            "\n" . '<loc>' . rex_yrewrite::getFullPath($path[$clang_id]) . '</loc>' .
                            "\n" . '<lastmod>' . date(DATE_W3C, $article->getValue('updatedate')) . '</lastmod>' . // serverzeitzone passt
                            "\n" . '<changefreq>' . $changefreq . '</changefreq>' .
                            "\n" . '<priority>' . $priority . '</priority>' .
                            "\n" . '</url>';
                    }
                }
            }
            $sitemap = rex_extension::registerPoint(new rex_extension_point('YREWRITE_DOMAIN_SITEMAP', $sitemap, ['domain' => $domain]));
        }
        $sitemap = rex_extension::registerPoint(new rex_extension_point('YREWRITE_SITEMAP', $sitemap));

        header('Content-Type: application/xml');
        $content = '<?xml version="1.0" encoding="UTF-8"?>';
        $content .= "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $content .= implode("\n", $sitemap);
        $content .= "\n" . '</urlset>';
        echo $content;
        exit;
    }

    public static function checkArticlePerm($article)
    {
        $perm = TRUE;
        if (class_exists('rex_com_auth'))
        {
            $perm = rex_com_auth::checkPerm($article);
            if ($perm == FALSE)
            {
                return FALSE;
            }
        }
        $perm = rex_extension::registerPoint(new rex_extension_point('YREWRITE_ARTICLE_PERM', $perm, ['article' => $article]));
        return $perm;
    }
}
