<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @author gregor.harlan@redaxo.org
 * @package redaxo4.5
 */

class rex_yrewrite
{

    /*
    * TODOS:
    * - call_by_article_id: forward, not_allowed
    */

    /** @var rex_yrewrite_domain[][] */
    static $domainsByMountId = array();

    /** @var rex_yrewrite_domain[] */
    static $domainsByName = array();

    static $aliasDomains = array();
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

    static function init()
    {
        global $REX;

        self::$domainsByMountId = array();
        self::$domainsByName = array();
        self::$aliasDomains = array();
        self::$paths = array();
        self::addDomain(new rex_yrewrite_domain('undefined', 0, $REX['START_ARTICLE_ID'], $REX['NOTFOUND_ARTICLE_ID']));
        self::$pathfile = $REX['INCLUDE_PATH'] . '/generated/files/yrewrite_pathlist.php';
        self::$configfile = $REX['INCLUDE_PATH'] . '/generated/files/yrewrite_config.php';
        self::readConfig();
        self::readPathFile();
    }

    // ----- domain

    static function addDomain(rex_yrewrite_domain $domain)
    {
        foreach ($domain->getClangs() as $clang) {
            self::$domainsByMountId[$domain->getMountId()][$clang] = $domain;
        }
        self::$domainsByName[$domain->getName()] = $domain;
    }

    static function addAliasDomain($from_domain, $to_domain, $clang_start = 0)
    {
        if (isset(self::$domainsByName[$to_domain])) {
            self::$aliasDomains[$from_domain] = array(
                'domain' => self::$domainsByName[$to_domain],
                'clang_start' => $clang_start
            );
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

    static function getDomainByArticleId($aid, $clang = 0)
    {
        foreach (self::$domainsByName as $name => $domain) {
            if (isset(self::$paths['paths'][$name][$aid][$clang])) {
                return $domain;
            }
        }
        return self::$domainsByName['undefined'];
    }

    static function getArticleIdByUrl($domain, $url)
    {
        if ($domain instanceof rex_yrewrite_domain) {
            $domain = $domain->getName();
        }
        foreach (self::$paths['paths'][$domain] as $c_article_id => $c_o) {
            foreach ($c_o as $c_clang => $c_url) {
                if ($url == $c_url) {
                    return array($c_article_id => $c_clang);
                }
            }
        }
        return false;

    }

    static function isDomainStartarticle($aid, $clang = 0)
    {
        foreach (self::$domainsByMountId as $d) {
            if (isset($d[$clang]) && $d[$clang]->getStartId() == $aid) {
                return true;
            }
        }

        return false;

    }

    static function isDomainMountpoint($aid, $clang = 0)
    {
        return isset(self::$domainsByMountId[$aid][$clang]);
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
                if (!isset($_SERVER['REQUEST_URI'])) {
                    $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
                    if (isset($_SERVER['QUERY_STRING'])) {
                        $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
                    }
                }
                $url = urldecode($_SERVER['REQUEST_URI']);
            }


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

            $host = self::getHost();

            $http = 'http://';
            if (self::isHttps()) {
              $http = 'https://';
            }


            if (isset(self::$paths['paths'][$host])) {
                $domain = self::$domainsByName[$host];
            } else {
                // check for aliases
                if (isset(self::$aliasDomains[$host])) {
                    /** @var rex_yrewrite_domain $domain */
                    $domain = self::$aliasDomains[$host]['domain'];
                    if (!$url && isset(self::$paths['paths'][$domain->getName()][$domain->getStartId()][self::$aliasDomains[$host]['clang_start']])) {
                        $url = self::$paths['paths'][$domain->getName()][$domain->getStartId()][self::$aliasDomains[$host]['clang_start']];
                    }
                    // forward to original domain permanent move 301

                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: ' . $http . $domain->getName() . '/' . $url);
                    exit;

                // no domain, no alias, domain with root mountpoint ?
                } elseif (isset(self::$domainsByMountId[0][$clang])) {
                    $domain = self::$domainsByMountId[0][$clang];

                // no root domain -> undefined
                } else {
                    $domain = self::$domainsByName['undefined'];
                }
            }

            $REX['DOMAIN_ARTICLE_ID'] = $domain->getMountId();
            $REX['START_ARTICLE_ID'] = $domain->getStartId();
            $REX['NOTFOUND_ARTICLE_ID'] = $domain->getNotfoundId();
            $REX['SERVER'] = $domain->getName();

            // if no path -> startarticle
            if ($url == '') {
                $REX['ARTICLE_ID'] = $domain->getStartId();
                $REX['CUR_CLANG'] = $domain->getStartClang();
                return true;
            }

            // normal exact check
            foreach (self::$paths['paths'][$domain->getName()] as $i_id => $i_cls) {

                foreach ($REX['CLANG'] as $clang_id => $clang_name) {
                    if (isset($i_cls[$clang_id]) && ($i_cls[$clang_id] == $url || $i_cls[$clang_id] . '/' == $url)) {
                        $REX['ARTICLE_ID'] = $i_id;
                        $REX['CUR_CLANG'] = $clang_id;
                        return true;
                    }
                }

            }

            $params = rex_register_extension_point('YREWRITE_PREPARE', '', array('url' => $url, 'domain' => $domain, 'http' => $http));

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

            // no article found -> domain not found article
            $REX['ARTICLE_ID'] = $domain->getNotfoundId();
            $REX['CUR_CLANG'] = $domain->getStartClang();
            foreach (self::$paths['paths'][$domain->getName()][$domain->getStartId()] as $clang => $clangUrl) {
                if ($clang != $domain->getStartClang() && 0 === strpos($url, $clangUrl)) {
                    $REX['CUR_CLANG'] = $clang;
                    break;
                }
            }

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

        $id    = $params['id'];
        $clang = $params['clang'];

        if (isset(self::$paths['redirections'][$id][$clang])) {
            $params['id']    = self::$paths['redirections'][$id][$clang]['id'];
            $params['clang'] = self::$paths['redirections'][$id][$clang]['clang'];
            return self::rewrite($params, $yparams, $fullpath);
        }

        //$url = urldecode($_SERVER['REQUEST_URI']);
        $domain = $_SERVER['HTTP_HOST'];

        $www = 'http://';
        if (self::isHttps()) {
            $www = 'https://';
        }

        $path = '';

        // same domain id check
        if (!$fullpath && isset(self::$paths['paths'][$domain][$id][$clang])) {
            $path = '/' . self::$paths['paths'][$domain][$id][$clang];
            // if($REX["REDAXO"]) { $path = rex_yrewrite::$paths['paths'][$domain][$id][$clang]; }
        }

        if ($path == '') {
            foreach (self::$paths['paths'] as $i_domain => $i_id) {
                if (isset(self::$paths['paths'][$i_domain][$id][$clang])) {
                    if ($i_domain == 'undefined') {
                        $path = '/' . self::$paths['paths'][$i_domain][$id][$clang];
                    } else {
                        $path = $www . $i_domain . '/' . self::$paths['paths'][$i_domain][$id][$clang];
                    }
                    break;
                }
            }
        }

        // params
        $urlparams = isset($params['params']) ? $params['params'] : '';
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

        $setDomain = function (rex_yrewrite_domain &$domain, &$path, OORedaxo $element) {
            $id = $element->getId();
            $clang = $element->getClang();
            if (isset(rex_yrewrite::$domainsByMountId[$id][$clang])) {
                $domain = rex_yrewrite::$domainsByMountId[$id][$clang];
                $path = rex_yrewrite::$scheme->getClang($clang, $domain);
            }
        };

        $setPath = function (rex_yrewrite_domain $domain, $path, OOArticle $art) use ($setDomain) {
            $setDomain($domain, $path, $art);
            if (($redirection = rex_yrewrite::$scheme->getRedirection($art, $domain)) instanceof OORedaxo) {
                rex_yrewrite::$paths['redirections'][$art->getId()][$art->getClang()] = array(
                    'id'    => $redirection->getId(),
                    'clang' => $redirection->getClang()
                );
                unset(rex_yrewrite::$paths['paths'][$domain->getName()][$art->getId()][$art->getClang()]);
                return;
            }
            unset(rex_yrewrite::$paths['redirections'][$art->getId()][$art->getClang()]);
            $url = rex_yrewrite::$scheme->getCustomUrl($art, $domain);
            if (!is_string($url)) {
                $url = rex_yrewrite::$scheme->appendArticle($path, $art, $domain);
            }
            rex_yrewrite::$paths['paths'][$domain->getName()][$art->getId()][$art->getClang()] = ltrim($url, '/');
        };

        $generatePaths = function (rex_yrewrite_domain $domain, $path, OOCategory $cat) use (&$generatePaths, $setDomain, $setPath) {
            $path = rex_yrewrite::$scheme->appendCategory($path, $cat, $domain);
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
                foreach (self::$paths['paths'] as $domain => $c) {
                    unset(self::$paths['paths'][$domain][$params['id']]);
                }
                unset(self::$paths['redirections'][$params['id']]);
                if (0 == $params['re_id']) {
                    break;
                }
                $params['id'] = $params['re_id'];
            // no break
            case 'CAT_ADDED':
            case 'CAT_UPDATED':
            case 'CAT_STATUS':
            case 'ART_ADDED':
            case 'ART_UPDATED':
            case 'ART_STATUS':
                rex_deleteCacheArticle($params['id']);
                $domain = self::$domainsByMountId[0][$params['clang']];
                $path = self::$scheme->getClang($params['clang'], $domain);
                $art = OOArticle::getArticleById($params['id'], $params['clang']);
                $tree = $art->getParentTree();
                if ($art->isStartArticle()) {
                    $cat = array_pop($tree);
                }
                foreach ($tree as $parent) {
                    $path = self::$scheme->appendCategory($path, $parent, $domain);
                    $setDomain($domain, $path, $parent);
                    $setPath($domain, $path, OOArticle::getArticleById($parent->getId(), $parent->getClang()));
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
            //case 'ALL_GENERATED':
            default:
                self::$paths = array('paths' => array(), 'redirections' => array());
                foreach ($REX['CLANG'] as $clangId => $clangName) {
                    $domain = self::$domainsByMountId[0][$clangId];
                    $path = self::$scheme->getClang($clangId, $domain);
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
        $domains = $gc->getArray('select * from rex_yrewrite_domain order by alias_domain, mount_id, clangs');
        foreach ($domains as $domain) {
            if ($domain['domain'] != '') {
                if ($domain['alias_domain'] != '') {
                    $filecontent .= "\n" . 'rex_yrewrite::addAliasDomain("' . $domain['domain'] . '", "' . $domain['alias_domain'] . '", ' . $domain['clang_start'] . ');';
                } elseif ($domain['start_id'] > 0 && $domain['notfound_id'] > 0) {
                    $filecontent .= "\n" . 'rex_yrewrite::addDomain(new rex_yrewrite_domain('
                        . '"' . $domain['domain'] . '", '
                        . $domain['mount_id'] . ', '
                        . $domain['start_id'] . ', '
                        . $domain['notfound_id'] . ', '
                        . (strlen(trim($domain['clangs'])) ? 'array(' . $domain['clangs'] . ')' : 'null') . ', '
                        . $domain['clang_start'] . ', '
                        . '"' . htmlspecialchars($domain['title_scheme']) . '", '
                        . '"' . htmlspecialchars($domain['description']) . '", '
                        . '"' . htmlspecialchars($domain['robots']) . '"'
                        . '));';
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

    static function isHttps()
    {
        if ( $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ) {
            return true;
        }
        return false;
    }

    static function deleteCache()
    {
        rex_generateAll();
    }

    static function getFullPath($link = '')
    {
        $domain = self::getHost();
        $http = 'http://';
        if (self::isHttps()) {
            $http = 'https://';
        }
        return $http . $domain . '/' . $link;
    }

    static function getHost()
    {
        return $_SERVER['HTTP_HOST'];
    }

}
