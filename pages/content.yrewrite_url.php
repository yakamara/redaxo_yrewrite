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

// TODO: content/yrewrite_url: { title: 'translate:mode_url', perm: 'yrewrite[url]' }

ob_start();

$addon = rex_addon::get('yrewrite');

$article_id = $params['article_id'];
$clang = $params['clang'];
$ctype = $params['ctype'];

// $yrewrite_url = stripslashes(rex_request('yrewrite_url'));
$domain = rex_yrewrite::getDomainByArticleId($article_id, $clang);
$isStartarticle = rex_yrewrite::isDomainStartArticle($article_id, $clang);

$autoUrl = rex_getUrl();
if (0 === strpos($autoUrl, $domain->getUrl())) {
    $autoUrl = substr($autoUrl, strlen($domain->getUrl()));
} else {
    $autoUrl = substr($autoUrl, strlen($domain->getPath()));
}

if ($isStartarticle) {

    echo rex_view::warning($addon->i18n('startarticleisalways', $domain->getName()));

} else {

    $yform = new rex_yform();
    $yform->setObjectparams('form_action', rex_url::backendController(['page' => 'content/edit', 'article_id' => $article_id, 'clang' => $clang, 'ctype' => $ctype], false));
    $yform->setObjectparams('form_id', 'yrewrite-url');
    $yform->setObjectparams('form_name', 'yrewrite-url');
    $yform->setHiddenField('yrewrite_func', 'url');

    $yform->setObjectparams('form_showformafterupdate', 1);

    $yform->setObjectparams('main_table', rex::getTable('article'));
    $yform->setObjectparams('main_id', $article_id);
    $yform->setObjectparams('main_where', 'id='.$article_id.' and clang_id='.$clang);
    $yform->setObjectparams('getdata', true);

    $yform->setValueField('text', ['yrewrite_url', $addon->i18n('customurl'), 'notice' => $autoUrl]);

    $yform->setValidateField('customfunction', ['name'=>'yrewrite_url', 'function' => function($func, $yrewrite_url ) {
        return (substr($yrewrite_url, 0, 1) == '/' || substr($yrewrite_url, -1) == '/');
    }, 'params'=>[], 'message' => rex_i18n::msg('yrewrite_warning_noslash')]);


    $yform->setValidateField('customfunction', ['name'=>'yrewrite_url', 'function' => function($func, $yrewrite_url ) {
        return (strlen($yrewrite_url) > 250);
    }, 'params'=>[], 'message' => rex_i18n::msg('yrewrite_warning_nottolong')]);


    $yform->setValidateField('customfunction', ['name'=>'yrewrite_url', 'function' => function($func, $yrewrite_url ) {
        return (!preg_match('/^[%_\.+\-\/a-zA-Z0-9]+$/', $yrewrite_url));
    }, 'params'=>[], 'message' => rex_i18n::msg('yrewrite_warning_chars')]);

    $yform->setValidateField('customfunction', ['name'=>'yrewrite_url', 'function' => function($func, $yrewrite_url, $params, $field ) {
        $return = (($a = rex_yrewrite::getArticleIdByUrl($params["domain"], $yrewrite_url)) && (key($a) != $params["article_id"] || current($a) != $params["clang"]));
        if ($return) {
            $field->setElement("message", rex_i18n::msg('yrewrite_warning_urlexists', key($a) ));
        }
        return $return;
    }, 'params'=>['article_id' => $article_id, "domain" => $domain, "clang" => $clang], 'message' => rex_i18n::msg('yrewrite_warning_urlexists')]);

    $yform->setActionField('db', [rex::getTable('article'), 'id=' . $article_id.' and clang_id='.$clang]);
    $yform->setObjectparams('submit_btn_label', $addon->i18n('update_url'));
    $form = $yform->getForm();

    if ($yform->objparams['actions_executed']) {
        $form = rex_view::success($addon->i18n('urlupdated')) . $form;
        rex_yrewrite::generatePathFile([
            'id' => $article_id,
            'clang' => $clang,
            'extension_point' => 'ART_UPDATED',
        ]);
        rex_article_cache::delete($article_id, $clang);

    } else {

    }

    echo  '<section id="rex-page-sidebar-yrewrite-url" data-pjax-container="#rex-page-sidebar-yrewrite-url" data-pjax-no-history="1">'.$form.'</section>';

    $selector_preview = '#yform-yrewrite-url-yrewrite_url p.help-block';
    $selector_url = '#yform-yrewrite-url-yrewrite_url input';

    echo '

<script type="text/javascript">

jQuery(document).ready(function() {

    jQuery("'.$selector_url.'").keyup(function() {
        updateCustomUrlPreview();
    });

    updateCustomUrlPreview();

});

function updateCustomUrlPreview() {
    var base = "'.('default' == $domain->getName() ? '&lt;default&gt;/' : $domain->getUrl()).'";
    var autoUrl = "'.$autoUrl.'";
    var customUrl = jQuery("'.$selector_url.'").val();
    var curUrl = "";

    if (customUrl !== "") {
        curUrl = base + customUrl;

    } else {
        curUrl = base + autoUrl;

    }

    jQuery("'.$selector_preview.'").html(curUrl);
}

</script>';

}

$form = ob_get_contents();
$content = '<section id="rex-page-sidebar-yrewrite-url" data-pjax-container="#rex-page-sidebar-yrewrite-url" data-pjax-no-history="1">'.$form.'</section>';
ob_end_clean();

return $content;
