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
  static $pathfile = "";
  static $configfile = "";
  static $call_by_article_id = 'allowed'; // forward, allowed, not_allowed
  static $pathes = array();
  static $scheme = "classic"; // "path";


  static function setLevenshtein($use_levenshtein = true) {
      self::$use_levenshtein = $use_levenshtein;
  }

  static function init() 
  {
    global $REX;
    rex_yrewrite::setDomain("default", 0, $REX["START_ARTICLE_ID"], $REX["NOTFOUND_ARTICLE_ID"]);
    self::$pathfile = $REX['INCLUDE_PATH'].'/generated/files/yrewrite_pathlist.php';
    self::$configfile = $REX['INCLUDE_PATH'].'/generated/files/yrewrite_config.php';
    self::readConfig();
    self::readPathFile();
  }

  // ----- domain

  static function setDomain($name, $domain_article_id, $start_article_id, $notfound_article_id) 
  {
    self::$domainsByMountId[$domain_article_id] = array(
      "domain" => $name,
      "domain_article_id" => $domain_article_id, 
      "start_article_id" => $start_article_id, 
      "notfound_article_id" => $notfound_article_id,
    );
    self::$domainsByName[$name] = array(
      "domain" => $name,
      "domain_article_id" => $domain_article_id, 
      "start_article_id" => $start_article_id, 
      "notfound_article_id" => $notfound_article_id,
    );
  }

  static function setAliasDomain($from_domain, $to_domain) 
  {
    if(isset(self::$domainsByName[$to_domain])) {
      self::$AliasDomains[$from_domain] = $to_domain;
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
    foreach(self::$domainsByName as $domain => $v) {
      if(isset(self::$pathes[$domain][$aid])) {
        return $domain;
      }
    }
    return "default";
  }  

  static function getArticleIdByUrl($domain, $url) 
  {
    foreach(self::$pathes[$domain] as $c_article_id => $c_o) {
      foreach($c_o as $c_clang => $c_url) {
        if($url == $c_url) {
          return array($c_article_id => $c_clang);
        }
      }
    }
    return false;

  }

  static function isDomainStartarticle($aid) 
  {
    foreach(self::$domainsByMountId as $d) {
      if($d["start_article_id"] == $aid) {
        return true;
      }
    }
  
    return false;
  
  }

  // ----- url
  
  static function prepare()
  {
    global $REX;

		$article_id = -1;
		$clang = $REX["CUR_CLANG"];

    // REXPATH wird auch im Backend benötigt, z.B. beim bearbeiten von Artikeln

    if (!$REX['REDAXO']) {

      // call_by_article allowed
      if(self::$call_by_article_id == "allowed" && rex_request("article_id","int")>0) {
        $url = rex_getUrl(rex_request("article_id","int"));

      } else {
        $url = urldecode($_SERVER['REQUEST_URI']);

      }

      $domain = $_SERVER['HTTP_HOST'];
      $port = $_SERVER['SERVER_PORT'];
      
      // because of server differences
      if (substr($url,0,1) == '/') {
        $url = substr($url, 1);
      }
      
      // delete params
      if(($pos = strpos($url, '?')) !== false) {
        $url = substr($url, 0, $pos);
      }
  
      // delete anker
      if(($pos = strpos($url, '#')) !== false) {
        $url = substr($url, 0, $pos);
      }

      // no domain found -> set default
      if(!isset(self::$pathes[$domain])) {
      
        // check for aliases
        if(isset(self::$AliasDomains[$domain])) {
          $domain = self::$AliasDomains[$domain];
          // forward to original domain permanent move 301
          
          $http = "http://";
          if($_SERVER["SERVER_PORT"] == 443) {
            $http = "https://";
          }

          header ('HTTP/1.1 301 Moved Permanently');
          header ('Location: '.$http.$domain.'/'.$url);
          exit;
          
        } else {
          $domain = "default";
        }
      }

      $REX['DOMAIN_ARTICLE_ID'] = self::$domainsByName[$domain]['domain_article_id'];
      $REX['START_ARTICLE_ID'] = self::$domainsByName[$domain]['start_article_id'];
      $REX['NOTFOUND_ARTICLE_ID'] = self::$domainsByName[$domain]['notfound_article_id'];
      $REX['SERVER'] = $domain;

      // if no path -> startarticle
      if($url == "") {
        $REX['ARTICLE_ID'] = self::$domainsByName[$domain]['start_article_id'];
        return true;
      }

      // normal exact check
      foreach(self::$pathes[$domain] as $i_id => $i_cls) {
      
        foreach($REX['CLANG'] as $clang => $clang_name) {
          if($i_cls[$clang] == $url || $i_cls[$clang].'/' == $url) {
            $REX['ARTICLE_ID'] = $i_id;
            $REX['CUR_CLANG'] = $clang;
            return true;
          }
        }

      }

      if(rex_register_extension_point('YREWRITE_PREPARE', '', array()) ) {
        return true;
      }

      // Check levenshtein
      if (self::$use_levenshtein)
      {
      /*
        foreach (self::$pathes as $key => $var) {
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
    if(isset($params['subject']) && $params['subject'] != '') {
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

    $www = "http://";
    if($port == 443) {
      $www = "https://";
    }

    $path = "";

    // same domain id check
    if(!$fullpath && isset(self::$pathes[$domain][$id][$clang])) {
      $path = '/'.self::$pathes[$domain][$id][$clang];
      // if($REX["REDAXO"]) { $path = self::$pathes[$domain][$id][$clang]; }
    }

    if($path == "") {
      foreach(self::$pathes as $i_domain => $i_id) {
        if(isset(self::$pathes[$i_domain][$id][$clang])) {
          if($i_domain == "default")
            $path = '/'.self::$pathes[$i_domain][$id][$clang];
          else 
            $path = $www.$i_domain.'/'.self::$pathes[$i_domain][$id][$clang];
          break;
        }
      }
    }

    // params
    $urlparams = $urlparams == '' ? '' : '?'.substr($urlparams,1,strlen($urlparams));
    $urlparams = str_replace('/amp;','/',$urlparams);
    $urlparams = str_replace('?amp;','?',$urlparams);

    return $path.$urlparams;

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
  	
    if(!isset($params['extension_point'])) {
      $params['extension_point'] = '';
    }
      
    $where = '';
    switch($params['extension_point']) {
    
      // clang and id specific update
      case 'CAT_DELETED':
      case 'ART_DELETED':
        foreach(self::$pathes as $domain => $c) {
          unset(self::$pathes[$domain][$params['id']]);
        }
        break;
      case 'CAT_ADDED':
      case 'CAT_UPDATED':
      case 'ART_ADDED':
      case 'ART_UPDATED':
        $where = '(id='. $params['id'] .' AND clang='. $params['clang'] .') OR (path LIKE "%|'. $params['id'] .'|%" AND clang='. $params['clang'] .')';
        break;
      // update_all / ARTICLE_GENERATED
      case 'CLANG_ADDED':
      case 'CLANG_UPDATED':
      case 'CLANG_DELETED':
      case 'ALL_GENERATED':
      default:
        $where = '1=1';
        self::$pathes = array();
  			break;
    }
    
    if($where != '') {
      $db = new rex_sql();
      // $db->debugsql=true;
      $db->setQuery('SELECT id,clang,path,startpage,yrewrite_url FROM '. $REX['TABLE_PREFIX'] .'article WHERE '. $where.' and revision=0');
      
      while($db->hasNext())
      {
      
        $pathname = '';
        $id = $db->getValue('id');
        $clang = $db->getValue('clang');
        
        if (array_key_exists($id, self::$domainsByMountId)) {
          $domain = self::$domainsByMountId[$id]["domain"];
        } else {
          // _____ pfad über kategorien bauen
          $domain = "default";
          $path = trim($db->getValue('path'), '|');
          if($path != '') {
            $path = explode('|', $path);
            $path = array_reverse($path,true);
          
            foreach ($path as $p) {
              if(array_key_exists($p, self::$domainsByMountId)) {
                $domain = self::$domainsByMountId[$p]["domain"];
                break;
              }
              $ooc = OOCategory::getCategoryById($p, $clang);

              $name = $ooc->getName();
              $pathname = rex_yrewrite::prependToPath($pathname, $name);
            }
          }
        }

        // _____ URL SCHEME
        
        if(self::$scheme == "path") {
        
          if(self::$domainsByName[$domain]["start_article_id"] == $db->getValue('id')) {
            $pathname = '';
          } else {
            $ooa = OOArticle::getArticleById($db->getValue('id'), $clang);
            if($ooa->isStartArticle()) {
              $ooc = $ooa->getCategory();
              $pathname = rex_yrewrite::appendToPath($pathname, $ooc->getName());
            } else {
      				$ooa = OOArticle::getArticleById($db->getValue('id'), $clang);
      				$pathname = rex_yrewrite::appendToPath($pathname, $ooa->getName());
      			}
            $pathname = preg_replace('/[-]{1,}/', '-', $pathname);
          }
          
        } else {
        
          if(self::$domainsByName[$domain]["start_article_id"] == $db->getValue('id')) {
            $pathname = '';
          } else {
            $ooa = OOArticle::getArticleById($db->getValue('id'), $clang);
            if($ooa->isStartArticle()) {
              $ooc = $ooa->getCategory();
              $pathname = rex_yrewrite::appendToPath($pathname, $ooc->getName());
            } else {
      				$pathname = rex_yrewrite::appendToPath($pathname, $ooa->getName());
      			}
        		$pathname = preg_replace('/[-]{1,}/', '-', $pathname);
            $pathname = substr($pathname,0,strlen($pathname)-1).'.html';
          }      
        }

        // _____ langkey first
        
        if (count($REX['CLANG']) > 1) {
          $pathname = $REX['CLANG'][$clang].'/'.$pathname;
        }

        if($db->getValue('yrewrite_url') != "") {
          $pathname = $db->getValue('yrewrite_url');
        }

        self::$pathes[$domain][$db->getValue('id')][$db->getValue('clang')] = $pathname;

        $db->next();
      }
    }
    
    rex_put_file_contents(self::$pathfile, json_encode(self::$pathes) );
  }
  
  static function appendToPath($path, $name)
  {
    if ($name != '') {
      $name = strtolower(rex_parse_article_name($name));
      $path .= $name.'/';
    }
    return $path;
  }
  
  static function prependToPath($path, $name)
  {
    if ($name != '') {
      $name = strtolower(rex_parse_article_name($name));
      $path = $name.'/'.$path;
    }
    return $path;
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
    $filecontent = '<?php '."\n";
    $gc = rex_sql::factory();
    $domains = $gc->getArray('select * from rex_yrewrite_domain');
    foreach($domains as $domain) {
      if($domain["domain"] != "") {
        if($domain["alias_domain"] != "") {
          $filecontent .= "\n".'rex_yrewrite::setAliasDomain("'.$domain["domain"].'", "'.$domain["alias_domain"].'");';
        } else if ($domain["mount_id"] > 0 && $domain["start_id"] > 0 && $domain["notfound_id"] > 0){
          $filecontent .= "\n".'rex_yrewrite::setDomain("'.$domain["domain"].'", '.$domain["mount_id"].', '.$domain["start_id"].', '.$domain["notfound_id"].');';
        }
      }
    }
    rex_put_file_contents(self::$configfile, $filecontent);
  }
  
  static function readConfig() 
  {
    if(!file_exists(self::$configfile)) {
      rex_yrewrite::generateConfig();
    }
    include self::$configfile;
  }
  
  static function readPathFile() 
  {
    if(!file_exists(self::$pathfile)) {
      self::generatePathFile(array());
    }
    $content = file_get_contents(self::$pathfile);
    self::$pathes = json_decode($content, true);
  } 

  static function copyHtaccess() {
    global $REX;
    $src = $REX["INCLUDE_PATH"].'/addons/yrewrite/setup/.htaccess';
    $des = $REX["INCLUDE_PATH"].'/../../.htaccess';
    copy ($src, $des);
  }


}

