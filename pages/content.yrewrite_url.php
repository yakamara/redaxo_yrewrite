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

ob_start();

$addon = rex_addon::get('yrewrite');
$article_id = $params['article_id'];
$clang = $params['clang'];

$yrewrite_url = stripslashes(rex_request('yrewrite_url'));
$domain = rex_yrewrite::getDomainByArticleId($article_id, $clang);
$isStartarticle = rex_yrewrite::isDomainStartarticle($article_id, $clang);

$sql = rex_sql::factory();
$data = $sql->getArray('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id=' . $article_id . ' AND clang_id=' . $clang);
$data = $data[0];

if (rex_post('save', 'boolean') == 1 && !$isStartarticle) {
    $url_status = true;

    if ($yrewrite_url == '') {
    } elseif (substr($yrewrite_url, 0, 1) == '/' or substr($yrewrite_url, -1) == '/') {
        echo rex_view::warning($addon->i18n('warning_noslash'));
        $url_status = false;

    } elseif (strlen($yrewrite_url) > 250) {
        echo rex_view::warning($addon->i18n('warning_nottolong'));
        $url_status = false;

    } elseif (!preg_match('/^[%_\.+\-\/a-zA-Z0-9]+$/', $yrewrite_url)) {
        echo rex_view::warning($addon->i18n('warning_chars'));
        $url_status = false;

    } elseif (($a = rex_yrewrite::getArticleIdByUrl($domain, $yrewrite_url)) && (key($a) != $article_id || current($a) != $clang)) {
        $art = '<a href="index.php?page=content&article_id='.key($a).'&mode=edit&clang='.current($a).'&ctype=1">'.$addon->i18n('warning_otherarticle').'</a>';

        echo rex_view::warning($addon->i18n('warning_urlexists', $art));
        $url_status = false;

    }

    if ($url_status) {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('article'));
        // $sql->debugsql = 1;
        $sql->setWhere('id=' . $article_id . ' AND clang_id=' . $clang);
        $sql->setValue('yrewrite_url', $yrewrite_url);
        if ($sql->update()) {
            rex_yrewrite::generatePathFile([
                'id' => $article_id,
                'clang' => $clang,
                'extension_point' => 'ART_UPDATED',
            ]);

            echo rex_view::info($addon->i18n('urlupdated'));
        }
    }

} else {
    $yrewrite_url = $data['yrewrite_url'];
}

if ($isStartarticle) {
    echo rex_view::warning($addon->i18n('startarticleisalways', $domain->getName()));

} else {

    $formElements = [];
    $n = [];
    $n['label'] = '<label for="rex-id-yrewrite-custom-url">' . $addon->i18n('customurl') . '</label>';
    $n['field'] = '<input class="form-control" id="rex-id-yrewrite-custom-url" type="text" name="yrewrite_url" value="' . htmlspecialchars($yrewrite_url) . '" />';
    $n['after'] = '<div id="rex-js-yrewrite-custom-url-preview"></div>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('flush', true);
    $fragment->setVar('elements', $formElements, false);
    $panel = $fragment->parse('core/form/form.php');


    $formElements = [];
    $n = [];
    $n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="1"' . rex::getAccesskey($addon->i18n('update'), 'save') . '>' . $addon->i18n('update') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');


    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $addon->i18n('rewriter'), false);
    $fragment->setVar('body', $panel, false);
    $fragment->setVar('buttons', $buttons, false);
    // $content = $fragment->parse('core/page/section.php');
    $content = $panel;

    echo '
        <form action="' . '' . '" method="post">
            ' . $content . '
        </form>';
    ?>
<script type="text/javascript">
jQuery(document).ready(function() {

    jQuery('#rex-id-yrewrite-custom-url').keyup(function() {
        updateCustomUrlPreview();
    });

    updateCustomUrlPreview();

});

function updateCustomUrlPreview() {
    var base = 'http[s]://<?php echo $domain->getName();
    ?>/';
    var autoUrl = '<?php
        $url = rex_getUrl();
    $url = str_replace('http://' . $domain->getName(), '', $url);
    $url = str_replace('https://' . $domain->getName(), '', $url);
    $url = substr($url, 1);
    echo $url;
    ?>';


    var customUrl = jQuery('#rex-id-yrewrite-custom-url').val();
    var curUrl = '';

    if (customUrl !== '') {
        curUrl = base + customUrl;
    } else {
        curUrl = base + autoUrl;
    }

    jQuery('#rex-js-yrewrite-custom-url-preview').html(curUrl);
}

</script> <?php

}

$content = ob_get_contents();
ob_end_clean();

return $content;
