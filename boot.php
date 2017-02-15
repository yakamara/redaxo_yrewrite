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

if(!rex::isBackend()) {
    $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), DIRECTORY_SEPARATOR) . '/';
    rex_url::init(new rex_path_default_provider($path, "redaxo", false));
}

// Additional permissions for url & seo editing
rex_perm::register('yrewrite[url]', rex_i18n::msg('yrewrite_perm_url_edit'));
rex_perm::register('yrewrite[seo]', rex_i18n::msg('yrewrite_perm_seo_edit'));

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

    if (rex::isBackend()) {

        if (!$this->getConfig('yrewrite_hide_url_block') && rex::getUser() instanceof rex_user && rex::getUser()->hasPerm('yrewrite[url]')) {
            rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', function (rex_extension_point $ep) {
                $params = $ep->getParams();
                $subject = $ep->getSubject();

                $panel = include(rex_path::addon('yrewrite', 'pages/content.yrewrite_url.php'));

                $fragment = new rex_fragment();
                $fragment->setVar('title', '<i class="rex-icon rex-icon-info"></i> '.rex_i18n::msg('yrewrite_rewriter'), false);
                $fragment->setVar('body', $panel, false);
                $fragment->setVar('article_id', $params["article_id"], false);

                $fragment->setVar('collapse', true);
                $fragment->setVar('collapsed', false);
                $content = $fragment->parse('core/page/section.php');

                return $subject.$content;

            });
        }

        if (!$this->getConfig('yrewrite_hide_seo_block') && rex::getUser() instanceof rex_user && rex::getUser()->hasPerm('yrewrite[seo]')) {
            rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', function (rex_extension_point $ep) {
                $params = $ep->getParams();
                $subject = $ep->getSubject();

                $panel = include(rex_path::addon('yrewrite', 'pages/content.yrewrite_seo.php'));

                $fragment = new rex_fragment();
                $fragment->setVar('title', '<i class="rex-icon rex-icon-info"></i> '.rex_i18n::msg('yrewrite_rewriter_seo'), false);
                $fragment->setVar('body', $panel, false);
                $fragment->setVar('article_id', $params["article_id"], false);
                $fragment->setVar('clang', $params["clang"], false);
                $fragment->setVar('ctype', $params["ctype"], false);
                $fragment->setVar('collapse', true);
                $fragment->setVar('collapsed', false);
                $content = $fragment->parse('core/page/section.php');

                return $subject.$content;

            });
        }

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
