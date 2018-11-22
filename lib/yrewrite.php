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

    /** @var rex_yrewrite_domain[] */
    private static $domainsById = [];

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

        if ($domain->getId()) {
            self::$domainsById[$domain->getId()] = $domain;
        }
    }

    public static function addAliasDomain($from_domain, $to_domain_id, $clang_start = 0)
    {
        if (isset(self::$domainsById[$to_domain_id])) {
            self::$aliasDomains[$from_domain] = [
                'domain' => self::$domainsById[$to_domain_id],
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

    /**
     * @param int $id
     * @return null|rex_yrewrite_domain
     */
    public static function getDomainById($id)
    {
        if (isset(self::$domainsById[$id])) {
            return self::$domainsById[$id];
        }
        return null;
    }


    public static function getDefaultDomain()
    {
        return self::$domainsByName['default'];
    }

    // ----- article

    public static function getCurrentDomain()
    {
        $article_id = rex_article::getCurrent()->getId();
        $clang_id = rex_clang::getCurrent()->getId();

        foreach (self::$domainsByName as $name => $domain) {
            if (isset(self::$paths['paths'][$name][$article_id][$clang_id])) {
                return $domain;
            }
        }
        return null;
    }

    public static function getFullUrlByArticleId($id, $clang = null, array $parameters = [], $separator = '&amp;')
    {
        $params = [];
        $params['id'] = $id;
        $params['clang'] = $clang ?: rex_clang::getCurrentId();
        $params['params'] = $parameters;
        $params['separator'] = $separator;

        return self::rewrite($params, [], true);
    }

    public static function getDomainByArticleId($aid, $clang = null)
    {
        $clang = $clang ?: rex_clang::getCurrentId();

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

    public static function isDomainStartArticle($aid, $clang = null)
    {
        $clang = $clang ?: rex_clang::getCurrentId();

        foreach (self::$domainsByMountId as $d) {
            if (isset($d[$clang]) && $d[$clang]->getStartId() == $aid) {
                return true;
            }
        }

        return false;
    }

    public static function isDomainMountpoint($aid, $clang = null)
    {
        $clang = $clang ?: rex_clang::getCurrentId();

        return isset(self::$domainsByMountId[$aid][$clang]);
    }

    public static function isInCurrentDomain($aid)
    {
        return (self::getDomainByArticleId($aid)->getName() == self::getCurrentDomain()->getName()) ? true : false;

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
        $params = '';
        if (($pos = strpos($url, '?')) !== false) {
            $params = substr($url, $pos);
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

                if (0 === strpos($url, $domain->getPath())) {
                    $url = substr($url, strlen($domain->getPath()));
                }

                $url = ltrim($url, '/');

                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $domain->getUrl() . $url . $params);
                exit;
            }

            if ('www.' === substr($host, 0, 4)) {
                $alternativeHost = substr($host, 4);
            } else {
                $alternativeHost = 'www.' . $host;
            }
            if (isset(self::$domainsByName[$alternativeHost])) {
                $domain = self::$domainsByName[$alternativeHost];

                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $domain->getUrl() . ltrim($url, '/') . $params);
                exit;
            }

            // no domain, no alias, domain with root mountpoint ?
            if (isset(self::$domainsByMountId[0][$clang])) {
                $domain = self::$domainsByMountId[0][$clang];

            // no root domain -> default
            } else {
                $domain = self::$domainsByName['default'];
            }
        }

        $currentScheme = self::isHttps() ? 'https' : 'http';
        $domainScheme = $domain->getScheme();
        if ($domainScheme && $domainScheme !== $currentScheme) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $domainScheme . '://' . $host . $url . $params);
            exit;
        }

        if (0 === strpos($url, $domain->getPath())) {
            $url = substr($url, strlen($domain->getPath()));
        }

        $url = ltrim($url, '/');

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
            if ($clang != $domain->getStartClang() && $clangUrl != '' && 0 === strpos($url, $clangUrl)) {
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
        $domainName = self::getHost();

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
                    $path = $domain->getUrl() . self::$paths['paths'][$i_domain][$id][$clang];
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
        $old_paths = self::$paths;
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
            case 'CAT_MOVED':
            case 'CAT_UPDATED':
            case 'CAT_STATUS':
            case 'ART_ADDED':
            case 'ART_COPIED':
            case 'ART_MOVED':
            case 'ART_UPDATED':
            case 'ART_META_UPDATED':
            case 'ART_STATUS':
            case 'ART_META_UPDATED':
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

        $sql = rex_sql::factory()
//                ->setDebug()
                ->setTable(rex::getTable('yrewrite_forward'));
        
        // Alte Einträge ausschalten
        
        $sql->setWhere('expiry_date > "0000-00-00" AND expiry_date < :date',['date'=>date('Y-m-d')]);
        $sql->setValue('status',0);
        $sql->update();
        
        // vergleicht alle Einträge aus old_paths mit der aktuellen path Liste.
        // nur ausführen, wenn es old_paths überhaupt gibt
        if ($old_paths) {
            foreach ($old_paths['paths'] as $domain_name => $old_article_paths) {
                $domain = rex_yrewrite::getDomainByName($domain_name);
                $domain_id = $domain->getId();
                $expiry_date = '0000-00-00';
                if ($domain->getAutoRedirectDays()) {
                    $expiry_date = date('Y-m-d',time()+$domain->getAutoRedirectDays()*24*60*60);            
                }

                // Autoredirect nicht setzen, wenn autoredirect für diese Domain nicht eingeschaltet ist
                if (!$domain->getAutoRedirect()) continue;
                foreach ($old_article_paths as $art_id => $old_paths ) {
                    foreach (rex_clang::getAllIds() as $clang_id) {
                        // Wenn es eine Abweichung im Pfad gibt, wird ein neuer Eintrag eingefügt
		                if (self::$paths['paths'][$domain_name][$art_id][$clang_id] != $old_paths[$clang_id]) {
		                	if($ep == 'CAT_DELETED' || $ep == 'ART_DELETED') {
			                    $params = [
			                        'article_id'=>$art_id
			                    ];
			                    $sql->setTable(rex::getTable('yrewrite_forward'));
			                    $sql->setWhere($params);
			                    $sql->delete();
			                }
			                else if($ep == 'CLANG_DELETED') {
			                    $params = [
			                        'clang'=>$clang_id
			                    ];
			                    $sql->setTable(rex::getTable('yrewrite_forward'));
			                    $sql->setWhere($params);
			                    $sql->delete();
							}
							else if($ep == 'CAT_MOVED' || $ep == 'CAT_UPDATED' || $ep == 'ART_MOVED' || $ep == 'ART_UPDATED' || $ep == 'ART_META_UPDATED') {
			                    $params = [
			                        'article_id'=>$art_id,
			                        'clang'=>$clang_id,
			                        'type'=>'article',
			                        'domain_id'=>$domain_id,
			                        'url'=>trim($old_paths[$clang_id],'/'),
			                        'movetype'=>'301',
			                        'status'=>1,
			                        'expiry_date' => $expiry_date
			                    ];
			                    $sql->setTable(rex::getTable('yrewrite_forward'));
			                    $sql->setValues($params);
			                    $sql->insert();

								// alte Redirects löschen wenn die URL der neuen URL des Artikels entspricht
			                    $params = [
			                        'url'=>trim(substr(rex_getUrl($art_id, $clang_id), strpos(rex_getUrl($art_id, $clang_id), $domain_name) + strlen($domain_name)), '/')
			                    ];
			                    $sql->setTable(rex::getTable('yrewrite_forward'));
			                    $sql->setValues([]);
			                    $sql->setWhere($params);
			                    $sql->delete();
			                }
                        }
                    }
                }            
            }
        }
        
        rex_yrewrite_forward::init();
        rex_yrewrite_forward::generatePathFile();
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

        $domains = $gc->getArray('select * from '.rex::getTable('yrewrite_domain').' order by mount_id, clangs');
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

            if ($domain['start_id'] > 0 && $domain['notfound_id'] > 0) {
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
                    . '"' . htmlspecialchars($domain['robots']) . '", '
                    . ($domain['clang_start_hidden'] ? 'true' : 'false') . ','
                    . $domain['id'] . ','
                    . $domain['auto_redirect'] . ','
                    . $domain['auto_redirect_days']
                    . '));';
            }
        }

        $domains = $gc->getArray('select * from '.rex::getTable('yrewrite_alias').' order by domain_id');
        foreach ($domains as $domain) {
            if (!$domain['alias_domain'] || !$domain['domain_id']) {
                continue;
            }

            $content .= "\n" . 'rex_yrewrite::addAliasDomain("' . $domain['alias_domain'] . '", ' . ((int) $domain['domain_id']) . ', ' . $domain['clang_start'] . ');';
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
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https") return true;
        return (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off');
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
        if (isset($_SERVER['HTTP_X_FORWARDED_SERVER'])) {
            return $_SERVER['HTTP_X_FORWARDED_SERVER'];
        }
        return @$_SERVER['HTTP_HOST'];
    }
}
