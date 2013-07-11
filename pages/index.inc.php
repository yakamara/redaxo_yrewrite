<?php

require $REX['INCLUDE_PATH'] . '/layout/top.php';

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');
$msg = '';

rex_title($I18N->msg('yrewrite'), $REX['ADDON']['pages']['yrewrite']);

// Include Current Page
switch ($subpage) {
    case ('domains');
        break;
    case ('alias_domains');
        break;
    case ('setup');
        break;
    case ('forward');
        break;
    default:
    {
        $subpage = 'domains';
    }
}

require dirname(__FILE__) . '/' . $subpage . '.inc.php';
require $REX['INCLUDE_PATH'] . '/layout/bottom.php';
