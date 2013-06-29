<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */

class rex_yrewrite
{

    /*
    * TODOS:
    * - call_by_article_id: forward, not_allowed
    */

    static $use_levenshtein = false;
    static $domainsByMountId = array();
    static $domainsByName = array();
    static $AliasDomains = array();
    static $pathfile = '';
    static $configfile = '';
    static $call_by_article_id = 'allowed'; // forward, allowed, not_allowed
    static $paths = array();
    /**
     * @var rex_yrewrite_scheme
     */
    static $scheme;

    static function setScheme(rex_yrewrite_scheme $scheme)
    {
        self::$scheme = $scheme;
    }


    static function setLevenshtein($use_levenshtein = true)
    {
        self::$use_levenshtein = $use_levenshtein;
    }

    static function init()
    {
        global $REX;
        self::setDomain('undefined', 0, $REX['START_ARTICLE_ID'], $REX['NOTFOUND_ARTICLE_ID']);
        self::$pathfile = $REX['INCLUDE_PATH'] . '/generated/files/yrewrite_pathlist.php';
        self::$configfile = $REX['INCLUDE_PATH'] . '/generated/files/yrewrite_config.php';
        self::readConfig();
        self::readPathFile();
    }

    // ----- domain

    static function setDomain($name, $domain_article_id, $start_article_id, $notfound_article_id)
    {
        self::$domainsByMountId[$domain_article_id] = array(
            'domain' => $name,
            'domain_article_id' => $domain_article_id,
            'start_article_id' => $start_article_id,
            'notfound_article_id' => $notfound_article_id,
        );
        self::$domainsByName[$name] = array(
            'domain' => $name,
            'domain_article_id' => $domain_article_id,
            'start_article_id' => $start_article_id,
            'notfound_article_id' => $notfound_article_id,
        );
    }

    static function setAliasDomain($from_domain, $to_domain)
    {
        if (isset(self::$domainsByName[$to_domain])) {
            self::$AliasDomains[$from_domain] = $to_domain;
        }
    }

    // ----- article

    static function getFullURLbyArticleId($id, $clang = 0)
    {
        $params = array();
        $params['id'] = $id;
        $params['clang'] = $clang;

        return self::rewrite($params, array(), true);
    }

    static function getDomainByArticleId($aid)
    {
        foreach (self::$domainsByName as $domain => $v) {
            if (isset(self::$paths[$domain][$aid])) {
                return $domain;
            }
        }
        return 'undefined';
    }

    static function getArticleIdByUrl($domain, $url)
    {
        foreach (self::$paths[$domain] as $c_article_id => $c_o) {
            foreach ($c_o as $c_clang => $c_url) {
                if ($url == $c_url) {
                    return array($c_article_id => $c_clang);
                }
            }
        }
        return false;

    }

    static function isDomainStartarticle($aid)
    {
        foreach (self::$domainsByMountId as $d) {
            if ($d['start_article_id'] == $aid) {
                return true;
            }
        }

        return false;

    }

    static function isDomainMountpoint($aid)
    {
        return isset(self::$domainsByMountId[$aid]);
    }

    // ----- url

    static function prepare()
    {
        global $REX;

        $article_id = -1;
        $clang = $REX['CUR_CLANG'];

        // REXPATH wird auch im Backend benötigt, z.B. beim bearbeiten von Artikeln

        if (!$REX['REDAXO']) {

            // call_by_article allowed
            if (self::$call_by_article_id == 'allowed' && rex_request('article_id', 'int') > 0) {
                $url = rex_getUrl(rex_request('article_id', 'int'));

            } else {
                $url = urldecode($_SERVER['REQUEST_URI']);

            }

            $domain = $_SERVER['HTTP_HOST'];
            $port = $_SERVER['SERVER_PORT'];

            // because of server differences
            if (substr($url, 0, 1) == '/') {
                $url = substr($url, 1);
            }

            // delete params
            if (($pos = strpos($url, '?')) !== false) {
                $url = substr($url, 0, $pos);
            }

            // delete anker
            if (($pos = strpos($url, '#')) !== false) {
                $url = substr($url, 0, $pos);
            }

            // no domain found -> set undefined
            if (!isset(self::$paths[$domain])) {

                // check for aliases
                if (isset(self::$AliasDomains[$domain])) {
                    $domain = self::$AliasDomains[$domain];
                    // forward to original domain permanent move 301

                    $http = 'http://';
                    if ($_SERVER['SERVER_PORT'] == 443) {
                        $http = 'https://';
                    }

                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: ' . $http . $domain . '/' . $url);
                    exit;

                } else {
                    $domain = 'undefined';
                }
            }

            $REX['DOMAIN_ARTICLE_ID'] = self::$domainsByName[$domain]['domain_article_id'];
            $REX['START_ARTICLE_ID'] = self::$domainsByName[$domain]['start_article_id'];
            $REX['NOTFOUND_ARTICLE_ID'] = self::$domainsByName[$domain]['notfound_article_id'];
            $REX['SERVER'] = $domain;

            // if no path -> startarticle
            if ($url == '') {
                $REX['ARTICLE_ID'] = self::$domainsByName[$domain]['start_article_id'];
                return true;
            }

            // normal exact check
            foreach (self::$paths[$domain] as $i_id => $i_cls) {

                foreach ($REX['CLANG'] as $clang => $clang_name) {
                    if ($i_cls[$clang] == $url || $i_cls[$clang] . '/' == $url) {
                        $REX['ARTICLE_ID'] = $i_id;
                        $REX['CUR_CLANG'] = $clang;
                        return true;
                    }
                }

            }

            $params = rex_register_extension_point('YREWRITE_PREPARE', '', array());

            if (isset($params['article_id']) && $params['article_id'] > 0) {

                if (isset($params['clang']) && $params['clang'] > 0) {
                    $clang = $params['clang'];
                }

                if ( ($article = OOArticle::getArticleById($params['article_id'], $clang)) ) {
                    $REX['ARTICLE_ID'] = $params['article_id'];
                    $REX['CUR_CLANG'] = $clang;
                    return true;
                }

            }

            // Check levenshtein
            if (self::$use_levenshtein) {
            /*
                foreach (self::$paths as $key => $var) {
                    foreach ($var as $k => $v) {
                        $levenshtein[levenshtein($path, $v)] = $key.'#'.$k;
                    }
                }
                ksort($levenshtein);
                $best = explode('#', array_shift($levenshtein));
                rex_yrewrite::setArticleId($best[0]);
                $clang = $best[1];
            */
            }

            // no article found -> domain not found article
            $REX['ARTICLE_ID'] = self::$domainsByName[$domain]['notfound_article_id'];

            return true;
        }
    }

    static function rewrite($params = array(), $yparams = array(), $fullpath = false)
    {
        // Url wurde von einer anderen Extension bereits gesetzt
        if (isset($params['subject']) && $params['subject'] != '') {
            return $params['subject'];
        }

        global $REX;

        $id         = $params['id'];
        $name       = @$params['name'];
        $clang      = $params['clang'];
        $divider    = @$params['divider'];
        $urlparams  = @$params['params'];

        $url = urldecode($_SERVER['REQUEST_URI']);
        $domain = $_SERVER['HTTP_HOST'];
        $port = $_SERVER['SERVER_PORT'];

        $www = 'http://';
        if ($port == 443) {
            $www = 'https://';
        }

        $path = '';

        // same domain id check
        if (!$fullpath && isset(self::$paths[$domain][$id][$clang])) {
            $path = '/' . self::$paths[$domain][$id][$clang];
            // if($REX["REDAXO"]) { $path = self::$paths[$domain][$id][$clang]; }
        }

        if ($path == '') {
            foreach (self::$paths as $i_domain => $i_id) {
                if (isset(self::$paths[$i_domain][$id][$clang])) {
                    if ($i_domain == 'undefined') {
                        $path = '/' . self::$paths[$i_domain][$id][$clang];
                    } else {
                        $path = $www . $i_domain . '/' . self::$paths[$i_domain][$id][$clang];
                    }
                    break;
                }
            }
        }

        // params
        $urlparams = $urlparams == '' ? '' : '?' . substr($urlparams, 1, strlen($urlparams));
        $urlparams = str_replace('/amp;', '/', $urlparams);
        $urlparams = str_replace('?amp;', '?', $urlparams);

        return $path . $urlparams;

    }


    /*
    *
    *  function: generatePathFile
    *  - updates or generates the file-domain-path filelist
    *  -
    *
    */

    static function generatePathFile($params)
    {
        global $REX;

        $setDomain = function (&$domain, &$path, OORedaxo $element) {
            if (isset(self::$domainsByMountId[$element->getId()])) {
                $domain = self::$domainsByMountId[$element->getId()]['domain'];
                $path = self::$scheme->getClang($element->getClang());
            }
        };

        $setPath = function ($domain, $path, OOArticle $art) use ($setDomain) {
            $setDomain($domain, $path, $art);
            $url = self::$scheme->getCustomUrl($art);
            if (!is_string($url)) {
                $url = self::$scheme->appendArticle($path, $art);
            }
            self::$paths[$domain][$art->getId()][$art->getClang()] = ltrim($url, '/');
        };

        $generatePaths = function ($domain, $path, OOCategory $cat) use (&$generatePaths, $setDomain, $setPath) {
            $path = self::$scheme->appendCategory($path, $cat);
            $setDomain($domain, $path, $cat);
            foreach ($cat->getChildren() as $child) {
                $generatePaths($domain, $path, $child);
            }
            foreach ($cat->getArticles() as $art) {
                $setPath($domain, $path, $art);
            }
        };

        $ep = isset($params['extension_point']) ? $params['extension_point'] : '';
        switch ($ep) {
            // clang and id specific update
            case 'CAT_DELETED':
            case 'ART_DELETED':
                foreach (self::$paths as $domain => $c) {
                    unset(self::$paths[$domain][$params['id']]);
                }
                break;

            case 'CAT_ADDED':
            case 'CAT_UPDATED':
            case 'ART_ADDED':
            case 'ART_UPDATED':
                $domain = 'undefined';
                $path = self::$scheme->getClang($params['clang']);
                $art = OOArticle::getArticleById($params['id'], $params['clang']);
                $tree = $art->getParentTree();
                if ($art->isStartArticle()) {
                    $cat = array_pop($tree);
                }
                foreach ($tree as $parent) {
                    $path = self::$scheme->appendCategory($path, $parent);
                    $setDomain($domain, $path, $parent);
                }
                if ($art->isStartArticle()) {
                    $generatePaths($domain, $path, $cat);
                } else {
                    $setPath($domain, $path, $art);
                }
                break;

            // update all
            case 'CLANG_DELETED':
            case 'CLANG_ADDED':
            case 'CLANG_UPDATED':
            case 'ALL_GENERATED':
            default:
                self::$paths = array();
                foreach ($REX['CLANG'] as $clangId => $clangName) {
                    $domain = 'undefined';
                    $path = self::$scheme->getClang($clangId);
                    foreach (OOCategory::getRootCategories(false, $clangId) as $cat) {
                        $generatePaths($domain, $path, $cat);
                    }
                    foreach (OOArticle::getRootArticles(false, $clangId) as $art) {
                        $setPath($domain, $path, $art);
                    }
                }
                break;
        }

        rex_put_file_contents(self::$pathfile, json_encode(self::$paths));
    }


    // ----- func

    static function checkUrl($url)
    {
        if (!preg_match('/^[%_\.+\-\/a-zA-Z0-9]+$/', $url)) {
            return false;
        }
        return true;
    }


    // ----- generate

    static function generateConfig()
    {
        $filecontent = '<?php ' . "\n";
        $gc = rex_sql::factory();
        $domains = $gc->getArray('select * from rex_yrewrite_domain');
        foreach ($domains as $domain) {
            if ($domain['domain'] != '') {
                if ($domain['alias_domain'] != '') {
                    $filecontent .= "\n" . 'rex_yrewrite::setAliasDomain("' . $domain['domain'] . '", "' . $domain['alias_domain'] . '");';
                } elseif ($domain['mount_id'] > 0 && $domain['start_id'] > 0 && $domain['notfound_id'] > 0) {
                    $filecontent .= "\n" . 'rex_yrewrite::setDomain("' . $domain['domain'] . '", ' . $domain['mount_id'] . ', ' . $domain['start_id'] . ', ' . $domain['notfound_id'] . ');';
                }
            }
        }
        rex_put_file_contents(self::$configfile, $filecontent);
    }

    static function readConfig()
    {
        if (!file_exists(self::$configfile)) {
            self::generateConfig();
        }
        include self::$configfile;
    }

    static function readPathFile()
    {
        if (!file_exists(self::$pathfile)) {
            self::generatePathFile(array());
        }
        $content = file_get_contents(self::$pathfile);
        self::$paths = json_decode($content, true);
    }

    static function copyHtaccess()
    {
        global $REX;
        $src = $REX['INCLUDE_PATH'] . '/addons/yrewrite/setup/.htaccess';
        $des = $REX['INCLUDE_PATH'] . '/../../.htaccess';
        copy($src, $des);
    }


}
