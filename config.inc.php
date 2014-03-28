<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */

$mypage = 'yrewrite';

$REX['ADDON']['name'][$mypage] = 'YRewrite';
$REX['ADDON']['perm'][$mypage] = 'yrewrite[forward]';
$REX['ADDON']['version'][$mypage] = '1.1';
$REX['ADDON']['author'][$mypage] = 'Jan Kristinus';
$REX['ADDON']['supportpage'][$mypage] = 'www.redaxo.org/de/forum';
$REX['PERM'][] = 'yrewrite[forward]';

$UrlRewriteBasedir = dirname(__FILE__);
require_once $UrlRewriteBasedir . '/classes/class.rex_yrewrite.inc.php';
require_once $UrlRewriteBasedir . '/classes/class.rex_yrewrite_domain.inc.php';
require_once $UrlRewriteBasedir . '/classes/class.rex_yrewrite_scheme.inc.php';
require_once $UrlRewriteBasedir . '/classes/class.rex_yrewrite_forward.inc.php';
require_once $UrlRewriteBasedir . '/classes/class.rex_yrewrite_seo.inc.php';

rex_yrewrite::setScheme(new rex_yrewrite_scheme());

if ($REX['REDAXO']) {

    $I18N->appendFile($REX['INCLUDE_PATH'] . '/addons/' . $mypage . '/lang/');

    // ----- content page - url manipulation
    if ($REX['MOD_REWRITE'] !== false) {
        rex_register_extension('PAGE_CONTENT_MENU', function ($params) {
            global $REX, $I18N;
            $class = '';
            if ($params['mode'] == 'yrewrite_url') {
                $class = 'class="rex-active"';
            }
            $page = '<a ' . $class . ' href="index.php?page=content&amp;article_id=' . $params['article_id'] . '&amp;mode=yrewrite_url&amp;clang=' . $params['clang'] . '&amp;ctype=' . rex_request('ctype') . '">' . $I18N->msg('yrewrite_mode_url') . '</a>';
            array_splice($params['subject'], '-2', '-2', $page);

            $class = '';
            if ($params['mode'] == 'yrewrite_seo') {
              $class = 'class="rex-active"';
            }
            $page = '<a ' . $class . ' href="index.php?page=content&amp;article_id=' . $params['article_id'] . '&amp;mode=yrewrite_seo&amp;clang=' . $params['clang'] . '&amp;ctype=' . rex_request('ctype') . '">' . $I18N->msg('yrewrite_mode_seo') . '</a>';
            array_splice($params['subject'], '-2', '-2', $page);

            array_pop($params['subject']);
            $params['subject'][] = '<a href="' . rex_getUrl($params['article_id'], $params['clang']) . '" target="_blank">' . $I18N->msg('show') . '</a>';


            return $params['subject'];
        });

        rex_register_extension('PAGE_CONTENT_OUTPUT', function ($params) {
            global $REX, $I18N;

            if ($params['mode'] == 'yrewrite_url') {
                include $REX['INCLUDE_PATH'] . '/addons/yrewrite/pages/content_url.inc.php';
            } else if ($params['mode'] == 'yrewrite_seo') {
              include $REX['INCLUDE_PATH'] . '/addons/yrewrite/pages/content_seo.inc.php';
            }
        });

    }


    if( $REX['USER'] && $REX['USER']->isAdmin()) {

        // ----- backend pages for domains und urls
        $domainsPage = new rex_be_page($I18N->msg('yrewrite_domains'), array(
            'page' => 'yrewrite',
            'subpage' => ''
          )
        );
        $domainsPage->setHref('index.php?page=yrewrite&subpage=');

        $AliasDomainsPage = new rex_be_page($I18N->msg('yrewrite_alias_domains'), array(
            'page' => 'yrewrite',
            'subpage' => 'alias_domains'
          )
        );
        $AliasDomainsPage->setHref('index.php?page=yrewrite&subpage=alias_domains');

        $forwardPage = new rex_be_page($I18N->msg('yrewrite_forward'), array(
          'page' => 'yrewrite',
          'subpage' => 'forward'
          )
        );
        $forwardPage->setHref('index.php?page=yrewrite&subpage=forward');

        $setupPage = new rex_be_page($I18N->msg('yrewrite_setup'), array(
            'page' => 'yrewrite',
            'subpage' => 'setup'
          )
        );
        $setupPage->setHref('index.php?page=yrewrite&subpage=setup');

        $REX['ADDON']['pages'][$mypage] = array (
            $domainsPage, $AliasDomainsPage, $forwardPage, $setupPage
        );

    } else if( $REX['USER'] && $REX['USER']->hasPerm("yrewrite[forward]")) {

       // ----- backend pages for domains und urls

        $forwardPage = new rex_be_page($I18N->msg('yrewrite_forward'), array(
          'page' => 'yrewrite',
          'subpage' => 'forward'
          )
        );
        $forwardPage->setHref('index.php?page=yrewrite&subpage=forward');

        $REX['ADDON']['pages'][$mypage] = array (
            $forwardPage
        );



   }

}


if ($REX['MOD_REWRITE'] !== false && !$REX['SETUP']) {

    rex_register_extension('ADDONS_INCLUDED', function ($params) {

        global $REX;

        rex_yrewrite::init();

        if (rex_request("rex_yrewrite_func","string") == "robots") {
            $robots = new rex_yrewrite_seo();
            $robots->sendRobotsTxt();
        }

        // if anything changes -> refresh PathFile
        if ($REX['REDAXO']) {
            $extension = 'rex_yrewrite::generatePathFile';
            $extensionPoints = array(
                'CAT_ADDED',   'CAT_UPDATED',   'CAT_DELETED', 'CAT_STATUS',
                'ART_ADDED',   'ART_UPDATED',   'ART_DELETED', 'ART_STATUS',
                'CLANG_ADDED', 'CLANG_UPDATED', 'CLANG_DELETED',
                /*'ARTICLE_GENERATED'*/
                //'ALL_GENERATED'
            );
            foreach ($extensionPoints as $extensionPoint) {
                rex_register_extension($extensionPoint, $extension);
            }
        }
        //rex_register_extension('ALL_GENERATED', 'rex_yrewrite::init');
        rex_register_extension('URL_REWRITE', 'rex_yrewrite::rewrite');

        // get ARTICLE_ID from URL
        if (!$REX['REDAXO']) {
            rex_yrewrite::prepare();
        }

    }, '', REX_EXTENSION_EARLY);

    if (rex_request("rex_yrewrite_func","string") == "sitemap") {
        rex_register_extension('ADDONS_INCLUDED', function ($params) {
            $sitemap = new rex_yrewrite_seo();
            $sitemap->sendSitemap();
        }, '', REX_EXTENSION_LATE);
    }

    rex_register_extension('YREWRITE_PREPARE', function ($params) {
        return rex_yrewrite_forward::getForward($params);
    });


}
