<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */

class rex_yrewrite_forward
{

  static $pathfile = '';
  static $paths = array();

  static $movetypes = array(
    '301' => '301 - Moved Permanently',
    '303' => '303 - See Other',
    '307' => '307 - Temporary Redirect'
  );

  static function init()
  {
      global $REX;
      self::$pathfile = $REX['INCLUDE_PATH'] . '/generated/files/yrewrite_forward_pathlist.php';
      self::readPathFile();

  }

  // ------------------------------

  static function getForward($params) 
  {
        // Url wurde von einer anderen Extension bereits gesetzt
        if (isset($params['subject']) && $params['subject'] != '') {
            return $params['subject'];
        }
        
      rex_yrewrite_forward::init();

      $domain = $params["domain"];
      if($domain == "undefined") {
          $domain = "";
      }
      $url = $params["url"];

      foreach(self::$paths as $p) {
      
          if($p["domain"] == $domain && ( $p["url"] == $url || $p["url"] . '/' == $url) ) {
              $forward_url = "";
              if($p["type"] == "article" && ($art = OOArticle::getArticleById($p["article_id"],$p["clang"])) ) {
                  $forward_url = rex_getUrl($p["article_id"],$p["clang"]);

              } else if($p["type"] == "media" && ($media = OOMedia::getMediaByFileName($p["media"]))) {
                  $forward_url = '/files/'.$p["media"];

              } else if($p["type"] == "extern" && $p["extern"] != "") {
                  $forward_url = $p["extern"];

              }

              if($forward_url != "") {
                  header('HTTP/1.1 '.self::$movetypes[$p["movetype"]]);
                  header('Location: ' . $forward_url);
                  exit;
              }

          }
      
      }
     return false;

  }

  // ------------------------------

  static function readPathFile()
  {
      if (!file_exists(self::$pathfile)) {
          self::generatePathFile(array());

      } else {
        $content = file_get_contents(self::$pathfile);
        self::$paths = json_decode($content, true);

      }

  }

  static function generatePathFile()
  {
    $gc = rex_sql::factory();
    $content = $gc->getArray('select * from rex_yrewrite_forward');
    rex_put_file_contents(self::$pathfile, json_encode($content));
    
  }

}
