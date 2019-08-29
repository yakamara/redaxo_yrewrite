<?php

/**
 * YREWRITE Addon.
 *
 * @author jan.kristinus@yakamara.de
 *
 * @package redaxo\yrewrite
 */

class rex_yrewrite_forward
{
    public static $pathfile = '';
    public static $paths = [];

    public static $movetypes = [
        '301' => '301 - Moved Permanently',
        '302' => '302 - Found',
        '303' => '303 - See Other',
        '307' => '307 - Temporary Redirect',
    ];

    public static function init()
    {
        self::$pathfile = rex_path::addonCache('yrewrite', 'forward_pathlist.json');
        self::readPathFile();
    }

    // ------------------------------

    public static function getForward($params)
    {
        // Url wurde von einer anderen Extension bereits gesetzt
        if (isset($params['subject']) && $params['subject'] != '') {
            return $params['subject'];
        }

        self::init();

        /** @var rex_yrewrite_domain $domain */
        $domain = $params['domain'];
        $url = $params['url'];

        foreach (self::$paths as $p) {
            $forwardDomain = rex_yrewrite::getDomainById($p['domain_id']);

            if (!$forwardDomain || $forwardDomain !== $domain) {
                continue;
            }

            if ($p['url'] == $url || $p['url'] . '/' == $url) {
                $forward_url = '';
                if ($p['type'] == 'article' && ($art = rex_article::get($p['article_id'], $p['clang']))) {
                    $forward_url = rex_getUrl($p['article_id'], $p['clang']);
                } elseif ($p['type'] == 'media' && ($media = rex_media::get($p['media']))) {
                    $forward_url = rex_url::media($p['media']);
                } elseif ($p['type'] == 'extern' && $p['extern'] != '') {
                    $forward_url = $p['extern'];
                }

                if ($forward_url != '') {
                    header('HTTP/1.1 '.self::$movetypes[$p['movetype']]);
                    header('Location: ' . $forward_url);
                    exit;
                }
            }
        }
        return false;
    }

    // ------------------------------

    public static function readPathFile()
    {
        if (!file_exists(self::$pathfile)) {
            self::generatePathFile();
        } else {
            self::$paths = rex_file::getCache(self::$pathfile);
        }
    }

    public static function generatePathFile()
    {
        $gc = rex_sql::factory();
        $content = $gc->getArray('select * from '.rex::getTable('yrewrite_forward'));
        rex_file::put(self::$pathfile, json_encode($content));
    }
}
