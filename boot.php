<?php

/**
 * YREWRITE Addon.
 *
 * @author jan.kristinus@yakamara.de
 *
 * @package redaxo\yrewrite
 *
 * @var rex_addon $this
 */

rex_yrewrite::setScheme(new rex_yrewrite_scheme());

rex_extension::register('PACKAGES_INCLUDED', function ($params) {

    rex_yrewrite::init();

    if (rex_request('rex_yrewrite_func', 'string') == 'robots') {
        $robots = new rex_yrewrite_seo();
        $robots->sendRobotsTxt();
    }

    // if anything changes -> refresh PathFile
    if (rex::isBackend()) {
        $extensionPoints = [
            'CAT_ADDED',   'CAT_UPDATED',   'CAT_DELETED', 'CAT_STATUS',
            'ART_ADDED',   'ART_UPDATED',   'ART_DELETED', 'ART_STATUS',
            /*'CLANG_ADDED',*/ 'CLANG_UPDATED', /*'CLANG_DELETED',*/
            /*'ARTICLE_GENERATED'*/
            //'ALL_GENERATED'
        ];
        foreach ($extensionPoints as $extensionPoint) {
            rex_extension::register($extensionPoint, function (rex_extension_point $ep) {
                $params = $ep->getParams();
                $params['subject'] = $ep->getSubject();
                $params['extension_point'] = $ep->getName();
                rex_yrewrite::generatePathFile($params);
            });
        }
    }
    //rex_extension::register('ALL_GENERATED', 'rex_yrewrite::init');
    rex_extension::register('URL_REWRITE', function (rex_extension_point $ep) {
        $params = $ep->getParams();
        $params['subject'] = $ep->getSubject();
        return rex_yrewrite::rewrite($params);
    });

    // get ARTICLE_ID from URL
    if (!rex::isBackend()) {
        rex_yrewrite::prepare();
    }

}, rex_extension::EARLY);

if (rex_request('rex_yrewrite_func', 'string') == 'sitemap') {
    rex_extension::register('PACKAGES_INCLUDED', function ($params) {
        $sitemap = new rex_yrewrite_seo();
        $sitemap->sendSitemap();
    }, rex_extension::LATE);
}

rex_extension::register('YREWRITE_PREPARE', function (rex_extension_point $ep) {
    $params = $ep->getParams();
    $params['subject'] = $ep->getSubject();
    return rex_yrewrite_forward::getForward($params);
});
