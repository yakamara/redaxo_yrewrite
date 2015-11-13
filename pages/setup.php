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

$func = rex_request('func', 'string');

if ($func != '') {
    if ($func == 'htaccess') {
        rex_yrewrite::copyHtaccess();
        echo rex_view::info($this->i18n('htacces_hasbeenset'));
    }
}

$domains = [];

foreach (rex_yrewrite::$domainsByName as $name => $val) {
    if ($name != 'undefined') {
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
                <h3 class="rex-hl2">' . $this->i18n('setup') . '</h3>
                <div class="rex-area-content">
                    <h4 class="rex-hl3">' . $this->i18n('htaccess_set') . '</h4>
                    <p class="rex-tx1">' . $this->i18n('htaccess_info') . '</p>
                    <p class="rex-button"><a class="rex-button" href="'.rex_url::currentBackendPage(['func' => 'htaccess']).'">' . $this->i18n('htaccess_set') . '</a></p>
                </div>
            </div>

            <br />&nbsp;

            <div class="rex-area">
                <h3 class="rex-hl2">' . $this->i18n('info_headline') . '</h3>
                <div class="rex-area-content">
                    <p class="rex-tx1">' . $this->i18n('info_text') . '</p>
                </div>
            </div>

            <br />&nbsp;

            <div class="rex-area">
                <h3 class="rex-hl2">' . $this->i18n('info_seo') . '</h3>
                <div class="rex-area-content">
                    <p class="rex-tx1">' . $this->i18n('info_seo_text') . '

                      <br /><br />'.highlight_string('<?php
  $seo = new rex_yrewrite_seo();
  echo $seo->getTitleTag();
  echo $seo->getDescriptionTag();
  echo $seo->getRobotsTag();

?>', true).'
                    </p>
                </div>
            </div>

            <br />&nbsp;

            <div class="rex-area">
                <h3 class="rex-hl2">' . $this->i18n('info_sitemaprobots') . '</h3>
                <table class="rex-table"><tr><th>Domain</th><th>Sitemap</th><th>robots.txt</th></tr>'.implode('', $domains).'</table>
            </div>
            ';
