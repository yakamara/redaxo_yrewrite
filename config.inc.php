<?php

/**
 * URL-Rewrite Addon
 * @author jan.kristinus@yakmara.de
 * @package redaxo4.4
 */

$mypage = "yrewrite";
 
// $REX['ADDON']['name'][$mypage] = 'URL Rewrite';
$REX['ADDON']['version'][$mypage] = "1.0";
$REX['ADDON']['author'][$mypage] = "Jan Kristinus";
$REX['ADDON']['supportpage'][$mypage] = 'www.redaxo.org/de/forum';
 
if ($REX['MOD_REWRITE'] !== false)
{

  $UrlRewriteBasedir = dirname(__FILE__);
  require_once $UrlRewriteBasedir.'/classes/class.rex_yrewrite.inc.php';

  // rex_yrewrite::setDomain(domain, mount_id, start_id, 404_id, www);
  rex_yrewrite::setDomain("domain.de", 34, 1, 1, true);
  rex_yrewrite::setPathFile($REX['INCLUDE_PATH'].'/generated/files/pathlist.php');

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
  rex_yrewrite::prepare();
  rex_register_extension('URL_REWRITE', 'rex_yrewrite::rewrite');
  rex_register_extension('PAGE_CONTENT_MENU', 'rex_yrewrite::setShowLink');
  
}



























// 


/*
 rex_register_extension('REXSEO_POST_REWRITE','rexmulti_controller_include');
 function rexmulti_controller_include($params)
 {

   // echo '<pre>';var_dump($params);
 
   $domains = array(
     34 => array("host" => "pbs.modulvier.com", "replace" => "pbsmodulviercom"),
     69 => array("host" => "officeeasy.modulvier.com", "replace" => "officeeasymodulviercom"),
     74 => array("host" => "bhi.modulvier.com", "replace" => "bhimodulviercom")
   );
 
   if( ($a = OOArticle::getArticleById($params["article_id"])) ) {
     $a_ids = explode("|",$a->getPath());
     foreach($a_ids as $a_id) {
       if(array_key_exists($a_id,$domains)) {

         // SERVER_PORT: 80 // 443
         if($_SERVER["HTTP_HOST"] == $domains[$a_id]) {
           return $params["subject"];
         }
         
         $http = "http";
         if($_SERVER["SERVER_PORT"] == 443) $http = "https";
         
         return $http."://".$domains[$a_id]["host"].$params["subject"];
         exit;
         
         
         
       }
     } 
   }
   
   return $params["subject"];
 */