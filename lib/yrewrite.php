<?php

/**
 * YREWRITE Addon.
 *
 * @author jan.kristinus@yakamara.de
 * @author gregor.harlan@redaxo.org
 *
 * @package redaxo\yrewrite
 */

class rex_yrewrite
{
    /** @var rex_yrewrite_domain[][] */
    private static $domainsByMountId = [];

    /** @var rex_yrewrite_domain[] */
    private static $domainsByName = [];

    private static $aliasDomains = [];
    private static $pathfile = '';
    private static $configfile = '';
    private static $call_by_article_id = 'allowed'; // forward, allowed, not_allowed
    public static $paths = [];

    /** @var rex_yrewrite_scheme */
    private static $scheme;

    public static function init()
    {
        if (null === self::$scheme) {
            self::setScheme(new rex_yrewrite_scheme());
        }

        self::$domainsByMountId = [];
        self::$domainsByName = [];
        self::$aliasDomains = [];
        self::$paths = [];

        $path = dirname($_SERVER['SCRIPT_NAME']);
        if (rex::isBackend()) {
            $path = dirname($path);
        }
        $path = rtrim($path, '/') . '/';
        self::addDomain(new rex_yrewrite_domain('default', null, $path, 0, rex_article::getSiteStartArticleId(), rex_article::getNotfoundArticleId()));

        self::$pathfile = rex_path::addonCache('yrewrite', 'pathlist.json');
        self::$configfile = rex_path::addonCache('yrewrite', 'config.php');
        self::readConfig();
        self::readPathFile();
    }

    public static function getScheme()
    {
        return self::$scheme;
    }

    public static function setScheme(rex_yrewrite_scheme $scheme)
    {
        self::$scheme = $scheme;
    }

    // ----- domain

    public static function addDomain(rex_yrewrite_domain $domain)
    {
        foreach ($domain->getClangs() as $clang) {
            self::$domainsByMountId[$domain->getMountId()][$clang] = $domain;
        }
        self::$domainsByName[$domain->getName()] = $domain;
    }

    public static function addAliasDomain($from_domain, $to_domain, $clang_start = 0)
    {
        if (isset(self::$domainsByName[$to_domain])) {
            self::$aliasDomains[$from_domain] = [
                'domain' => self::$domainsByName[$to_domain],
                'clang_start' => $clang_start,
            ];
        }
    }

    /**
     * @return rex_yrewrite_domain[]
     */
    public static function getDomains()
    {
        return self::$domainsByName;
    }

    /**
     * @param string $name
     * @return null|rex_yrewrite_domain
     */
    public static function getDomainByName($name)
    {
        if (isset(self::$domainsByName[$name])) {
            return self::$domainsByName[$name];
        }
        return null;
    }

    // ----- article

    public static function getFullUrlByArticleId($id, $clang = 0)
    {
        $params = [];
        $params['id'] = $id;
        $params['clang'] = $clang;

        return self::rewrite($params, [], true);
    }

    public static function getDomainByArticleId($aid, $clang = 0)
    {
        foreach (self::$domainsByName as $name => $domain) {
            if (isset(self::$paths['paths'][$name][$aid][$clang])) {
                return $domain;
            }
        }
        return self::$domainsByName['default'];
    }

    public static function getArticleIdByUrl($domain, $url)
    {
        if ($domain instanceof rex_yrewrite_domain) {
            $domain = $domain->getName();
        }
        foreach (self::$paths['paths'][$domain] as $c_article_id => $c_o) {
            foreach ($c_o as $c_clang => $c_url) {
                if ($url == $c_url) {
                    return [$c_article_id => $c_clang];
                }
            }
        }
        return false;
    }

    public static function isDomainStartArticle($aid, $clang = 0)
    {
        foreach (self::$domainsByMountId as $d) {
            if (isset($d[$clang]) && $d[$clang]->getStartId() == $aid) {
                return true;
            }
        }

        return false;
    }

    public static function isDomainMountpoint($aid, $clang = 0)
    {
        return isset(self::$domainsByMountId[$aid][$clang]);
    }

    // ----- url

    public static function getPathsByDomain($domain)
    {
        return self::$paths['paths'][$domain];
    }

    public static function prepare()
    {
        $clang = rex_clang::getCurrentId();

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
        if (substr($url, 0, 1) != '/') {
            $url = '/' . $url;
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
                header('Location: ' . $domain->getUrl() . $url);
                exit;

            // no domain, no alias, domain with root mountpoint ?
            } elseif (isset(self::$domainsByMountId[0][$clang])) {
                $domain = self::$domainsByMountId[0][$clang];

            // no root domain -> default
            } else {
                $domain = self::$domainsByName['default'];
            }
        }

        if (0 === strpos($url, $domain->getPath())) {
            $url = substr($url, strlen($domain->getPath()));
        }

        $url = ltrim($url, '/');

        //rex::setProperty('domain_article_id', $domain->getMountId());
        rex::setProperty('server', $domain->getUrl());

        $structureAddon = rex_addon::get('structure');
        $structureAddon->setProperty('start_article_id', $domain->getStartId());
        $structureAddon->setProperty('notfound_article_id', $domain->getNotfoundId());

        // if no path -> startarticle
        if ($url === '') {
            $structureAddon->setProperty('article_id', $domain->getStartId());
            rex_clang::setCurrentId($domain->getStartClang());
            return true;
        }

        // normal exact check
        foreach (self::$paths['paths'][$domain->getName()] as $i_id => $i_cls) {
            foreach (rex_clang::getAllIds() as $clang_id) {
                if (isset($i_cls[$clang_id]) && $i_cls[$clang_id] == $url) {
                    $structureAddon->setProperty('article_id', $i_id);
                    rex_clang::setCurrentId($clang_id);
                    return true;
                }
            }
        }

        $candidates = self::$scheme->getAlternativeCandidates($url, $domain);
        if ($candidates) {
            foreach ((array) $candidates as $candidate) {
                foreach (self::$paths['paths'][$domain->getName()] as $i_id => $i_cls) {
                    foreach (rex_clang::getAllIds() as $clang_id) {
                        if (isset($i_cls[$clang_id]) && $i_cls[$clang_id] == $candidate) {
                            header('HTTP/1.1 301 Moved Permanently');
                            header('Location: ' . $domain->getPath() . $candidate);
                            exit;
                        }
                    }
                }
            }
        }

        $params = rex_extension::registerPoint(new rex_extension_point('YREWRITE_PREPARE', '', ['url' => $url, 'domain' => $domain]));

        if (isset($params['article_id']) && $params['article_id'] > 0) {
            if (isset($params['clang']) && $params['clang'] > 0) {
                $clang = $params['clang'];
            }

            if (($article = rex_article::get($params['article_id'], $clang))) {
                $structureAddon->setProperty('article_id', $params['article_id']);
                rex_clang::setCurrentId($clang);
                return true;
            }
        }

        // no article found -> domain not found article
        $structureAddon->setProperty('article_id', $domain->getNotfoundId());
        rex_clang::setCurrentId($domain->getStartClang());
        foreach (self::$paths['paths'][$domain->getName()][$domain->getStartId()] as $clang => $clangUrl) {
            if ($clang != $domain->getStartClang() && 0 === strpos($url, $clangUrl)) {
                rex_clang::setCurrentId($clang);
                break;
            }
        }

        return true;
    }

    public static function rewrite($params = [], $yparams = [], $fullpath = false)
    {
        // Url wurde von einer anderen Extension bereits gesetzt
        if (isset($params['subject']) && $params['subject'] != '') {
            return $params['subject'];
        }

        $id = $params['id'];
        $clang = $params['clang'];

        if (isset(self::$paths['redirections'][$id][$clang])) {
            $params['id'] = self::$paths['redirections'][$id][$clang]['id'];
            $params['clang'] = self::$paths['redirections'][$id][$clang]['clang'];
            return self::rewrite($params, $yparams, $fullpath);
        }

        //$url = urldecode($_SERVER['REQUEST_URI']);
        $domainName = $_SERVER['HTTP_HOST'];

        $path = '';

        // same domain id check
        if (!$fullpath && isset(self::$paths['paths'][$domainName][$id][$clang])) {
            $domain = self::getDomainByName($domainName);
            $path = $domain->getPath() . self::$paths['paths'][$domainName][$id][$clang];
            // if(rex::isBackend()) { $path = self::$paths['paths'][$domain][$id][$clang]; }
        }

        if ($path == '') {
            foreach (self::$paths['paths'] as $i_domain => $i_id) {
                if (isset(self::$paths['paths'][$i_domain][$id][$clang])) {
                    $domain = self::getDomainByName($i_domain);
                    if ($i_domain == 'default') {
                        $path = $domain->getPath() . self::$paths['paths'][$i_domain][$id][$clang];
                    } else {
                        $path = $domain->getUrl() . self::$paths['paths'][$i_domain][$id][$clang];
                    }
                    break;
                }
            }
        }

        // params
        $urlparams = '';
        if (isset($params['params'])) {
            $urlparams = rex_string::buildQuery($params['params'], $params['separator']);
        }

        return $path . ($urlparams ? '?' . $urlparams : '');
    }

    /*
    *
    *  function: generatePathFile
    *  - updates or generates the file-domain-path filelist
    *  -
    *
    */

    public static function generatePathFile($params)
    {
        $setDomain = function (rex_yrewrite_domain &$domain, &$path, rex_structure_element $element) {
            $id = $element->getId();
            $clang = $element->getClang();
            if (isset(self::$domainsByMountId[$id][$clang])) {
                $domain = self::$domainsByMountId[$id][$clang];
                $path = self::$scheme->getClang($clang, $domain);
            }
        };

        $setPath = function (rex_yrewrite_domain $domain, $path, rex_article $art) use ($setDomain) {
            $setDomain($domain, $path, $art);
            if (($redirection = self::$scheme->getRedirection($art, $domain)) instanceof rex_structure_element) {
                self::$paths['redirections'][$art->getId()][$art->getClang()] = [
                    'id' => $redirection->getId(),
                    'clang' => $redirection->getClang(),
                ];
                unset(self::$paths['paths'][$domain->getName()][$art->getId()][$art->getClang()]);
                return;
            }
            unset(self::$paths['redirections'][$art->getId()][$art->getClang()]);
            $url = self::$scheme->getCustomUrl($art, $domain);
            if (!is_string($url)) {
                $url = self::$scheme->appendArticle($path, $art, $domain);
            }
            self::$paths['paths'][$domain->getName()][$art->getId()][$art->getClang()] = ltrim($url, '/');
        };

        $generatePaths = function (rex_yrewrite_domain $domain, $path, rex_category $cat) use (&$generatePaths, $setDomain, $setPath) {
            $path = self::$scheme->appendCategory($path, $cat, $domain);
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
                rex_article_cache::delete($params['id']);
                $domain = self::$domainsByMountId[0][$params['clang']];
                $path = self::$scheme->getClang($params['clang'], $domain);
                $art = rex_article::get($params['id'], $params['clang']);
                $tree = $art->getParentTree();
                if ($art->isStartArticle()) {
                    $cat = array_pop($tree);
                }
                foreach ($tree as $parent) {
                    $path = self::$scheme->appendCategory($path, $parent, $domain);
                    $setDomain($domain, $path, $parent);
                    $setPath($domain, $path, rex_article::get($parent->getId(), $parent->getClang()));
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
                self::$paths = ['paths' => [], 'redirections' => []];
                foreach (rex_clang::getAllIds() as $clangId) {
                    $domain = self::$domainsByMountId[0][$clangId];
                    $path = self::$scheme->getClang($clangId, $domain);
                    foreach (rex_category::getRootCategories(false, $clangId) as $cat) {
                        $generatePaths($domain, $path, $cat);
                    }
                    foreach (rex_article::getRootArticles(false, $clangId) as $art) {
                        $setPath($domain, $path, $art);
                    }
                }
                break;
        }

        rex_file::putCache(self::$pathfile, self::$paths);
    }

    // ----- func

    public static function checkUrl($url)
    {
        if (!preg_match('/^[%_\.+\-\/a-zA-Z0-9]+$/', $url)) {
            return false;
        }
        return true;
    }

    // ----- generate

    public static function generateConfig()
    {
        $content = '<?php ' . "\n";
        $gc = rex_sql::factory();
        $domains = $gc->getArray('select * from '.rex::getTable('yrewrite_domain').' order by alias_domain, mount_id, clangs');
        foreach ($domains as $domain) {
            if (!$domain['domain']) {
                continue;
            }

            $name = $domain['domain'];
            if (false === strpos($name, '//')) {
                $name = '//'.$name;
            }
            $parts = parse_url($name);
            $name = $parts['host'];
            if (isset($parts['port'])) {
                $name .= ':'.$parts['port'];
            }
            $path = '/';
            if (isset($parts['path'])) {
                $path = rtrim($parts['path'], '/') . '/';
            }

            if ($domain['alias_domain'] != '') {
                $content .= "\n" . 'rex_yrewrite::addAliasDomain("' . $name . '", "' . $domain['alias_domain'] . '", ' . $domain['clang_start'] . ');';
            } elseif ($domain['start_id'] > 0 && $domain['notfound_id'] > 0) {
                $content .= "\n" . 'rex_yrewrite::addDomain(new rex_yrewrite_domain('
                    . '"' . $name . '", '
                    . (isset($parts['scheme']) ? '"'.$parts['scheme'].'"' : 'null') . ', '
                    . '"' . $path . '", '
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
        rex_file::put(self::$configfile, $content);
    }

    public static function readConfig()
    {
        if (!file_exists(self::$configfile)) {
            self::generateConfig();
        }
        include self::$configfile;
    }

    public static function readPathFile()
    {
        if (!file_exists(self::$pathfile)) {
            self::generatePathFile([]);
        }
        self::$paths = rex_file::getCache(self::$pathfile);
    }

    public static function copyHtaccess()
    {
        rex_file::copy(rex_path::addon('yrewrite', 'setup/.htaccess'), rex_path::frontend('.htaccess'));
    }

    public static function isHttps()
    {
        return $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off');
    }

    public static function deleteCache()
    {
        rex_delete_cache();
    }

    public static function getFullPath($link = '')
    {
        $domain = self::getHost();
        $http = 'http://';
        if (self::isHttps()) {
            $http = 'https://';
        }
        return $http . $domain . '/' . $link;
    }

    public static function getHost()
    {
        return $_SERVER['HTTP_HOST'];
    }
}
