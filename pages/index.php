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

echo rex_view::title(rex_i18n::msg('yrewrite'));

rex_be_controller::includeCurrentPageSubPath();
