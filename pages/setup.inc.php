<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */

if ( !$REX['MOD_REWRITE'] ) {
  echo rex_warning($I18N->msg("yrewrite_notactivebecauseofmodrewrite"));
}

$func = rex_request('func', 'string');

if ($func != '') {
    if ($func == 'htaccess') {
        rex_yrewrite::copyHtaccess();
        echo rex_info($I18N->msg('yrewrite_htacces_hasbeenset'));
    }
}

$domains = array();

foreach(rex_yrewrite::$domainsByName as $name => $val) {
    if($name != 'undefined') {
        $domains[] = '<tr><td><a href="http://'.$name.'">'.htmlspecialchars($name).'</a></td><td><a href="http://'.$name.'/sitemap.xml">sitemap.xml</a></td><td><a href="http://'.$name.'/robots.txt">robots.txt</a></td></tr>';
    }
}

echo '

            <style>
             .rex-area .rex-area-content p.rex-tx1 code{
              background-color:#dFe9e9;
              padding:10px;
              display:block
            }

            .rex-area .rex-area-content p.rex-tx1 b{
              background-color:#bFc9c9;
              padding:3px;
              color:#333;
            }

            </style>

            <div class="rex-area">
                <h3 class="rex-hl2">' . $I18N->msg('yrewrite_setup') . '</h3>
                <div class="rex-area-content">
                    <h4 class="rex-hl3">' . $I18N->msg('yrewrite_htaccess_set') . '</h4>
                    <p class="rex-tx1">' . $I18N->msg('yrewrite_htaccess_info') . '</p>
                    <p class="rex-button"><a class="rex-button" href="index.php?page=yrewrite&subpage=setup&func=htaccess">' . $I18N->msg('yrewrite_htaccess_set') . '</a></p>
                </div>
            </div>

            <br />&nbsp;

            <div class="rex-area">
                <h3 class="rex-hl2">' . $I18N->msg('yrewrite_info_headline') . '</h3>
                <div class="rex-area-content">
                    <p class="rex-tx1">' . $I18N->msg('yrewrite_info_text') . '</p>
                </div>
            </div>

            <br />&nbsp;

            <div class="rex-area">
                <h3 class="rex-hl2">' . $I18N->msg('yrewrite_info_seo') . '</h3>
                <div class="rex-area-content">
                    <p class="rex-tx1">' . $I18N->msg('yrewrite_info_seo_text') . '

                      <br /><br />'.highlight_string('<?php
  $seo = new rex_yrewrite_seo();
  echo $seo->getTitleTag();
  echo $seo->getDescriptionTag();
  echo $seo->getRobotsTag();
  
?>',true).'
                    </p>
                </div>
            </div>

            <br />&nbsp;

            <div class="rex-area">
                <h3 class="rex-hl2">' . $I18N->msg('yrewrite_info_sitemaprobots') . '</h3>
                <table class="rex-table"><tr><th>Domain</th><th>Sitemap</th><th>robots.txt</th></tr>'.implode('',$domains).'</table>
            </div>
            ';




