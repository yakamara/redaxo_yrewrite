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
    echo '
<div class="rex-content-body" id="yrewrite-contentpage">
    <div class="rex-content-body-2">
        <div class="rex-form" id="rex-form-content-metamode">
            <form action="'.$context->getUrl().'" method="post" enctype="multipart/form-data" id="yrewrite-form" name="yrewrite-form">
                <input type="hidden" name="save" value="1" />

                <fieldset class="rex-form-col-1">
                  <legend>' . $addon->i18n('rewriter') . '</legend>

                  <div class="rex-form-wrapper">

                        <div class="rex-form-row"><p class="rex-form-text" style="margin-bottom: -3px;">
                            <label for="custom-url">' . $addon->i18n('customurl') . '</label>
                            <input type="text" value="' . htmlspecialchars($yrewrite_url) . '" name="yrewrite_url" id="custom-url" class="rex-form-text">
                            </p>

                            <div style="display: inline-block;margin-left: 158px; margin-top: 12px; line-height: 25px;    margin-top: 10px;" id="custom-url-preview"></div>
                        </div>

                        <div class="rex-form-row">
                            <p class="rex-form-col-a rex-form-submit">
                                <input type="submit" value="' . $addon->i18n('update') . '" name="save" class="rex-form-submit">
                                <br/><br/>
                            </p>
                        </div>
                        <div class="rex-clearer"></div>
              </div>

          </fieldset>

            </form>
        </div>
    </div>
</div>';
    ?>
<script type="text/javascript">
jQuery(document).ready(function() {

    jQuery('#custom-url').keyup(function() {
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


    var customUrl = jQuery('#custom-url').val();
    var curUrl = '';

    if (customUrl !== '') {
        curUrl = base + customUrl;
    } else {
        curUrl = base + autoUrl;
    }

    jQuery('#custom-url-preview').html(curUrl);
}

</script> <?php

}

return ob_get_clean();
