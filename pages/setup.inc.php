<?php

$func = rex_request('func', 'string');

if ($func != '') {
    if ($func == 'htaccess') {
        rex_yrewrite::copyHtaccess();
        echo rex_info($I18N->msg('yrewrite_htacces_hasbeenset'));
    }
}

echo '
            <div class="rex-area">
                <h3 class="rex-hl2">' . $I18N->msg('yrewrite_setup') . '</h3>
                <div class="rex-area-content">
                    <h4 class="rex-hl3">' . $I18N->msg('yrewrite_htaccess_set') . '</h4>
                    <p class="rex-tx1">' . $I18N->msg('yrewrite_htaccess_info') . '</p>
                    <p class="rex-button"><a class="rex-button" href="index.php?page=yrewrite&subpage=setup&func=htaccess">' . $I18N->msg('yrewrite_htaccess_set') . '</a></p>
                    <h4 class="rex-hl3">' . $I18N->msg('yrewrite_info_headline') . '</h4>
                    <p class="rex-tx1">' . $I18N->msg('yrewrite_info_text') . '</p>
                </div>
            </div>';
