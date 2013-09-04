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
        rex_yrewrite::$scheme = $scheme;
    }

    static function init()
    {
        global $REX;
        rex_yrewrite::setDomain('undefined', 0, $REX['START_ARTICLE_ID'], $REX['NOTFOUND_ARTICLE_ID']);
        rex_yrewrite::$pathfile = $REX['INCLUDE_PATH'] . '/generated/files/yrewrite_pathlist.php';
        rex_yrewrite::$configfile = $REX['INCLUDE_PATH'] . '/generated/files/yrewrite_config.php';
        rex_yrewrite::readConfig();
        rex_yrewrite::readPathFile();
    }

    // ----- domain

    static function setDomain($name, $domain_article_id, $start_article_id, $notfound_article_id, $title_scheme = '', $description = '', $robots = '')
    {
        rex_yrewrite::$domainsByMountId[$domain_article_id] = array(
            'domain' => $name,
            'domain_article_id' => $domain_article_id,
            'start_article_id' => $start_article_id,
            'notfound_article_id' => $notfound_article_id,
            'robots' => $robots,
            'title_scheme' => $title_scheme,
            'description' => $description,
        );
        rex_yrewrite::$domainsByName[$name] = array(
            'domain' => $name,
            'domain_article_id' => $domain_article_id,
            'start_article_id' => $start_article_id,
            'notfound_article_id' => $notfound_article_id,
            'robots' => $robots,
            'title_scheme' => $title_scheme,
            'description' => $description,
        );
    }

    static function setAliasDomain($from_domain, $to_domain)
    {
        if (isset(rex_yrewrite::$domainsByName[$to_domain])) {
            rex_yrewrite::$AliasDomains[$from_domain] = $to_domain;
        }
    }

    // ----- article

    static function getFullURLbyArticleId($id, $clang = 0)
    {
        $params = array();
        $params['id'] = $id;
        $params['clang'] = $clang;

        return rex_yrewrite::rewrite($params, array(), true);
    }

    static function getDomainByArticleId($aid)
    {
        foreach (rex_yrewrite::$domainsByName as $domain => $v) {
            if (isset(rex_yrewrite::$paths['paths'][$domain][$aid])) {
                return $domain;
            }
        }
        return 'undefined';
    }

    static function getArticleIdByUrl($domain, $url)
    {
        foreach (rex_yrewrite::$paths['paths'][$domain] as $c_article_id => $c_o) {
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
        foreach (rex_yrewrite::$domainsByMountId as $d) {
            if ($d['start_article_id'] == $aid) {
                return true;
            }
        }

        return false;

    }

    static function isDomainMountpoint($aid)
    {
        return isset(rex_yrewrite::$domainsByMountId[$aid]);
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
            if (rex_yrewrite::$call_by_article_id == 'allowed' && rex_request('article_id', 'int') > 0) {
                $url = rex_getUrl(rex_request('article_id', 'int'));

            } else {
                if (!isset($_SERVER['REQUEST_URI'])) {
                    $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
                    if (isset($_SERVER['QUERY_STRING'])) {
                        $_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
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

            $domain = self::getHost();

            $http = 'http://';
            if(self::isHttps()) {
              $http = 'https://';
            }

            // no domain found -> set undefined
            if (!isset(rex_yrewrite::$paths['paths'][$domain])) {

                // check for aliases
                if (isset(rex_yrewrite::$AliasDomains[$domain])) {
                    $domain = rex_yrewrite::$AliasDomains[$domain];
                    // forward to original domain permanent move 301

                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: ' . $http . $domain . '/' . $url);
                    exit;

                // no domain, no alias, domain with root mountpoint ?
                } else if ( isset(rex_yrewrite::$domainsByMountId[0]) ) {
                    $domain = rex_yrewrite::$domainsByMountId[0]['domain'];

                // no root domain -> undefined
                } else {
                    $domain = 'undefined';
                }
            }

            $REX['DOMAIN_ARTICLE_ID'] = rex_yrewrite::$domainsByName[$domain]['domain_article_id'];
            $REX['START_ARTICLE_ID'] = rex_yrewrite::$domainsByName[$domain]['start_article_id'];
            $REX['NOTFOUND_ARTICLE_ID'] = rex_yrewrite::$domainsByName[$domain]['notfound_article_id'];
            $REX['SERVER'] = $domain;

            // if no path -> startarticle
            if ($url == '') {
                $REX['ARTICLE_ID'] = rex_yrewrite::$domainsByName[$domain]['start_article_id'];
                return true;
            }

            // normal exact check
            foreach (rex_yrewrite::$paths['paths'][$domain] as $i_id => $i_cls) {

                foreach ($REX['CLANG'] as $clang => $clang_name) {
                    if ($i_cls[$clang] == $url || $i_cls[$clang] . '/' == $url) {
                        $REX['ARTICLE_ID'] = $i_id;
                        $REX['CUR_CLANG'] = $clang;
                        return true;
                    }
                }

            }

            $params = rex_register_extension_point('YREWRITE_PREPARE', '', array('url' =>$url, 'domain' => $domain, 'http' => $http));

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
            $REX['ARTICLE_ID'] = rex_yrewrite::$domainsByName[$domain]['notfound_article_id'];

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

        if (isset(rex_yrewrite::$paths['redirections'][$id][$clang])) {
            $params['id']    = rex_yrewrite::$paths['redirections'][$id][$clang]['id'];
            $params['clang'] = rex_yrewrite::$paths['redirections'][$id][$clang]['clang'];
            return rex_yrewrite::rewrite($params, $yparams, $fullpath);
        }

        //$url = urldecode($_SERVER['REQUEST_URI']);
        $domain = $_SERVER['HTTP_HOST'];

        $www = 'http://';
        if(self::isHttps()) {
            $www = 'https://';
        }

        $path = '';

        // same domain id check
        if (!$fullpath && isset(rex_yrewrite::$paths['paths'][$domain][$id][$clang])) {
            $path = '/' . rex_yrewrite::$paths['paths'][$domain][$id][$clang];
            // if($REX["REDAXO"]) { $path = rex_yrewrite::$paths['paths'][$domain][$id][$clang]; }
        }

        if ($path == '') {
            foreach (rex_yrewrite::$paths['paths'] as $i_domain => $i_id) {
                if (isset(rex_yrewrite::$paths['paths'][$i_domain][$id][$clang])) {
                    if ($i_domain == 'undefined') {
                        $path = '/' . rex_yrewrite::$paths['paths'][$i_domain][$id][$clang];
                    } else {
                        $path = $www . $i_domain . '/' . rex_yrewrite::$paths['paths'][$i_domain][$id][$clang];
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

        $setDomain = function (&$domain, &$path, OORedaxo $element) {
            $element_id = $element->getId();
            if (isset(rex_yrewrite::$domainsByMountId[$element_id])) {
                $domain = rex_yrewrite::$domainsByMountId[$element_id]['domain'];
                $path = rex_yrewrite::$scheme->getClang($element->getClang());

            } else if ( $element->getParentId() == 0 ) {
                $domain = rex_yrewrite::$domainsByMountId[0]['domain'];
            }

        };

        $setPath = function ($domain, $path, OOArticle $art) use ($setDomain) {
            $setDomain($domain, $path, $art);
            if (($redirection = rex_yrewrite::$scheme->getRedirection($art)) instanceof OORedaxo) {
                rex_yrewrite::$paths['redirections'][$art->getId()][$art->getClang()] = array(
                    'id'    => $redirection->getId(),
                    'clang' => $redirection->getClang()
                );
                unset(rex_yrewrite::$paths['paths'][$domain][$art->getId()][$art->getClang()]);
                return;
            }
            unset(rex_yrewrite::$paths['redirections'][$art->getId()][$art->getClang()]);
            $url = rex_yrewrite::$scheme->getCustomUrl($art);
            if (!is_string($url)) {
                $url = rex_yrewrite::$scheme->appendArticle($path, $art);
            }
            rex_yrewrite::$paths['paths'][$domain][$art->getId()][$art->getClang()] = ltrim($url, '/');
        };

        $generatePaths = function ($domain, $path, OOCategory $cat) use (&$generatePaths, $setDomain, $setPath) {
            $path = rex_yrewrite::$scheme->appendCategory($path, $cat);
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
                foreach (rex_yrewrite::$paths['paths'] as $domain => $c) {
                    unset(rex_yrewrite::$paths['paths'][$domain][$params['id']]);
                }
                unset(rex_yrewrite::$paths['redirections'][$params['id']]);
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
                $domain = 'undefined';
                $path = rex_yrewrite::$scheme->getClang($params['clang']);
                $art = OOArticle::getArticleById($params['id'], $params['clang']);
                $tree = $art->getParentTree();
                if ($art->isStartArticle()) {
                    $cat = array_pop($tree);
                }
                foreach ($tree as $parent) {
                    $path = rex_yrewrite::$scheme->appendCategory($path, $parent);
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
            case 'ALL_GENERATED':
            default:
                rex_yrewrite::$paths = array('paths' => array(), 'redirections' => array());
                foreach ($REX['CLANG'] as $clangId => $clangName) {
                    $domain = 'undefined';
                    $path = rex_yrewrite::$scheme->getClang($clangId);
                    foreach (OOCategory::getRootCategories(false, $clangId) as $cat) {
                        $generatePaths($domain, $path, $cat);
                    }
                    foreach (OOArticle::getRootArticles(false, $clangId) as $art) {
                        $setPath($domain, $path, $art);
                    }
                }
                break;
        }

        rex_put_file_contents(rex_yrewrite::$pathfile, json_encode(rex_yrewrite::$paths));
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
                } elseif ($domain['start_id'] > 0 && $domain['notfound_id'] > 0) {
                    $filecontent .= "\n" . 'rex_yrewrite::setDomain(
                      "' . $domain['domain'] . '", ' . $domain['mount_id'] . ',
                      ' . $domain['start_id'] . ',
                      ' . $domain['notfound_id'] . ',
                      "'.htmlspecialchars($domain['title_scheme']).'",
                      "'.htmlspecialchars($domain['description']).'",
                      "'.htmlspecialchars($domain['robots']).'"
                      );';
                }
            }
        }
        rex_put_file_contents(rex_yrewrite::$configfile, $filecontent);
    }

    static function readConfig()
    {
        if (!file_exists(rex_yrewrite::$configfile)) {
            rex_yrewrite::generateConfig();
        }
        include rex_yrewrite::$configfile;
    }

    static function readPathFile()
    {
        if (!file_exists(rex_yrewrite::$pathfile)) {
            rex_yrewrite::generatePathFile(array());
        }
        $content = file_get_contents(rex_yrewrite::$pathfile);
        rex_yrewrite::$paths = json_decode($content, true);
    }

    static function copyHtaccess()
    {
        global $REX;
        $src = $REX['INCLUDE_PATH'] . '/addons/yrewrite/setup/.htaccess';
        $des = $REX['INCLUDE_PATH'] . '/../../.htaccess';
        copy($src, $des);
    }

    static function isHttps() {
      if ( $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ) {
        return true;
      }
      return false;
    }

    static function deleteCache() {
      rex_generateAll();
      self::$paths = array();
      self::init();
    }

    static function getFullPath($link = '')
    {
        $domain = self::getHost();
        $http = 'http://';
        if(self::isHttps()) {
          $http = 'https://';
        }
        return $http.$domain.'/'.$link;
    }

    static function getHost() {
      $domain = $_SERVER['HTTP_HOST'];
      if (!isset(rex_yrewrite::$paths['paths'][$domain])) {
        if (isset(rex_yrewrite::$AliasDomains[$domain])) {
          $domain = rex_yrewrite::$AliasDomains[$domain];
        }
      }
      return $domain;
    }

}
