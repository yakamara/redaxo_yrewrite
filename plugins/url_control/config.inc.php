<?php

/**
 *
 * @author blumbeet - web.studio
 * @author Thomas Blum
 * @author mail[at]blumbeet[dot]com Thomas Blum
 *
 */


$basedir = __DIR__;
$myself = 'url_control';

$addon = str_replace(DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $myself, '', $basedir);
$addon = ltrim(substr($addon, strrpos($addon, '/')), DIRECTORY_SEPARATOR);
$addon = strtolower($addon);

// Einstellungen für die Rewriter
$rewriter = array(
    'yrewrite' => array(
        'extension_point'       => 'YREWRITE_PREPARE',
        'extension_function'    => 'extension_rewriter_yrewrite',
        'pages'                 => true,
        'subpages'              => false,
        'get_url'               => 'rex_yrewrite::getFullUrlByArticleId',
    ),
    'rexseo' => array(
        'extension_point'       => 'REXSEO_ARTICLE_ID_NOT_FOUND',
        'extension_function'    => 'extension_rewriter_rexseo',
        'pages'                 => false,
        'subpages'              => true,
    ),
    'rexseo42' => array(
        'extension_point'       => 'REXSEO_ARTICLE_ID_NOT_FOUND',
        'extension_function'    => 'extension_rewriter_rexseo42',
        'pages'                 => false,
        'subpages'              => true,
    )
);



// Sprachdateien anhaengen
if ($REX['REDAXO']) {
    $I18N->appendFile($basedir . '/lang/');
}



$REX['ADDON']['rxid'][$myself]         = '';
//$REX['ADDON']['name'][$myself]         = $I18N->msg('b_url_generate_title');
$REX['ADDON']['version'][$myself]      = '0.0';
$REX['ADDON']['author'][$myself]       = 'blumbeet - web.studio';
$REX['ADDON']['supportpage'][$myself]  = '';
// $REX['ADDON']['perm'][$myself]         = 'url_control[]';
// $REX['PERM'][]                         = 'url_control[]';
$REX['ADDON'][$myself]['addon']        = $addon;
$REX['ADDON'][$myself]['rewriter']     = $rewriter;


$mysubpages = array('url_control_generate', 'url_control_manager');

//$REX['ADDON'][$addon]['SUBPAGES'][] = array ('url_generate' , $I18N->msg('b_url_generate'));
if (isset($REX['USER'])){ // && ($REX['USER']->isAdmin() || $REX['USER']->hasPerm('url_control[]'))) {

    if ($rewriter[$addon]['pages']) {
        foreach ($mysubpages as $mysubpage) {
            $be_page = new rex_be_page($I18N->msg('b_' . $mysubpage), array(
                    'page'      => $addon,
                    'subpage'   => $mysubpage
                )
            );
            $be_page->setHref('index.php?page=' . $addon . '&subpage=' . $mysubpage);
            $REX['ADDON']['pages'][$addon][] = $be_page;
        }
    }

    if ($rewriter[$addon]['subpages']) {
        foreach ($mysubpages as $mysubpage) {
            $REX['ADDON'][$addon]['SUBPAGES'][] = array($mysubpage, $I18N->msg('b_' . $mysubpage));
        }
    }

}

$subpage = rex_request('subpage', 'string');
if (rex_request('page', 'string') == $addon &&  in_array($subpage, $mysubpages)) {
    $file = str_replace('control_', '', $subpage);
    $REX['ADDON']['navigation'][$addon]['path'] = $REX['INCLUDE_PATH'].'/addons/' . $addon . '/plugins/' . $myself . '/pages/' . $file . '.php';
}


if ($REX['MOD_REWRITE'] !== false && !$REX['SETUP']) {
    require_once($basedir . '/lib/url_control.php');
    require_once($basedir . '/lib/url_generate.php');
    require_once($basedir . '/lib/url_manager.php');

    $extension_point    = $rewriter[$addon]['extension_point'];
    $extension_function = $rewriter[$addon]['extension_function'];
    rex_register_extension($extension_point, 'url_control::' . $extension_function);

    rex_register_extension('ADDONS_INCLUDED', 'url_control::extension_register_extensions', '', REX_EXTENSION_EARLY);

    url_control::init();
    //rex_register_extension('ADDONS_INCLUDED', 'url_control::init');

}

