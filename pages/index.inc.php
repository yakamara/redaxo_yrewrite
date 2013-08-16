<?php

require $REX['INCLUDE_PATH'] . '/layout/top.php';

$page = rex_request('page', 'string');
$request_subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');
$msg = '';

rex_title($I18N->msg('yrewrite'), $REX['ADDON']['pages']['yrewrite']);

$subpage = "forward";
foreach($REX['ADDON']['pages']['yrewrite'] as $p) {
    if (!isset($p->activateCondition["subpage"][$request_subpage])) {
        $subpage = $request_subpage;
        $_REQUEST["subpage"] = $subpage;
        break;
    };
}

if($subpage == "") {
  $subpage = "domains";
}

require dirname(__FILE__) . '/' . $subpage . '.inc.php';
require $REX['INCLUDE_PATH'] . '/layout/bottom.php';
