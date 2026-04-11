<?php
/**
 * Smarty function plugin: {banner_list}
 *
 * Scans the banners/ directory and returns an HTML list of links
 * to preview each banner on the donations page.
 *
 * Demo/dev only – not for production use.
 */
function smarty_function_banner_list($params, &$smarty)
{
    $templateDir = is_array($smarty->template_dir)
        ? $smarty->template_dir[0]
        : $smarty->template_dir;

    $bannersDir = $templateDir . '/banners';
    if (!is_dir($bannersDir)) {
        return '<!-- banners directory not found: ' . htmlspecialchars($bannersDir) . ' -->';
    }

    $files = glob($bannersDir . '/banner_*.tpl');
    if (empty($files)) {
        return '<!-- no banner templates found -->';
    }

    sort($files);

    $html = '<h1>Banner-Vorschau (Demo)</h1>' . "\n";
    $html .= '<ul>' . "\n";

    foreach ($files as $file) {
        $basename = basename($file, '.tpl');
        $key = preg_replace('/^banner_/', '', $basename);

        $html .= '  <li><a href="/articles.php?page=donations&amp;banner='
               . htmlspecialchars($key, ENT_QUOTES, 'UTF-8')
               . '">' . htmlspecialchars($basename, ENT_QUOTES, 'UTF-8')
               . '</a></li>' . "\n";
    }

    $html .= '</ul>' . "\n";

    return $html;
}
