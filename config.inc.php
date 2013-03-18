<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */


/*
* TODOS:

- clang integrieren  / domain.de -> aid:5,clang:1 / domain.en -> aid:2,clang:0
- wenn default .. dann keine Domain ausgeben
  - direkt in die klasse einbauen
- cache refresh wenn url neu geschrieben
- frau schultze einbauen (MarketingURLs)
- Verwaltung der URLs, DOmains über GUI bauen

*/


$mypage = "yrewrite";
 
// $REX['ADDON']['name'][$mypage] = 'URL Rewrite';
$REX['ADDON']['version'][$mypage] = "1.0";
$REX['ADDON']['author'][$mypage] = "Jan Kristinus";
$REX['ADDON']['supportpage'][$mypage] = 'www.redaxo.org/de/forum';

$I18N->appendFile($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/lang/');



if ($REX["REDAXO"]) {

  if ($REX['MOD_REWRITE'] !== false) {
    rex_register_extension('PAGE_CONTENT_MENU', function ($params) {
      global $REX, $I18N;
      $class = "";
      if ($params['mode'] == 'yrewrite') {
        $class = 'class="rex-active"';
      }
      $page = '<a '.$class.' href="index.php?page=content&amp;article_id=' . $params['article_id'] . '&amp;mode=yrewrite&amp;clang=' . $params['clang'] . '&amp;ctype=' . rex_request('ctype') . '">'.$I18N->msg("yrewrite_mode").'</a>';
      array_splice($params['subject'], '-2', '-2', $page);
    
      return $params['subject'];
    });
    
    rex_register_extension('PAGE_CONTENT_OUTPUT', function ($params) {
      global $REX, $I18N;
      
      if ($params['mode'] == 'yrewrite') {
        include($REX['INCLUDE_PATH'] . '/addons/yrewrite/pages/content.inc.php');
      }
    });
  
    rex_register_extension('PAGE_CONTENT_MENU', 'rex_yrewrite::setShowLink');

  }
}


if ($REX['MOD_REWRITE'] !== false && !$REX['SETUP']) {

  rex_register_extension('ADDONS_INCLUDED', function($params) {
  
    global $REX;

      $UrlRewriteBasedir = dirname(__FILE__);
      require_once $UrlRewriteBasedir.'/classes/class.rex_yrewrite.inc.php';
      rex_yrewrite::setDomain("default", 0, $REX["START_ARTICLE_ID"], $REX["NOTFOUND_ARTICLE_ID"]);
      rex_yrewrite::setPathFile($REX['INCLUDE_PATH'].'/generated/files/pathlist.php');

      // your setup
      // rex_yrewrite::setDomain(domain, mount_id, start_id, 404_id, [clang_id]);
      rex_yrewrite::setDomain("mydomain.de", 1, 2, 3);

      // alias domains
      // rex_yrewrite::setAliasDomain(from_domain, to_domain);
      rex_yrewrite::setAliasDomain("www.mydomain.de", "mydomain.de");

      // if anything changes -> refresh PathFile
      if ($REX['REDAXO']) {
        $extension = 'rex_yrewrite::generatePathFile';
        $extensionPoints = array(
          'CAT_ADDED',   'CAT_UPDATED',   'CAT_DELETED',
          'ART_ADDED',   'ART_UPDATED',   'ART_DELETED',
          'CLANG_ADDED', 'CLANG_UPDATED', 'CLANG_DELETED',
          /*'ARTICLE_GENERATED'*/
          'ALL_GENERATED');
        foreach($extensionPoints as $extensionPoint) {
          rex_register_extension($extensionPoint, $extension);
        }
      }

      // get ARTICLE_ID from URL
      if (!$REX["REDAXO"]) {
        rex_yrewrite::prepare();
      }
      rex_register_extension('URL_REWRITE', 'rex_yrewrite::rewrite');

  }, '', REX_EXTENSION_EARLY);

  
}
