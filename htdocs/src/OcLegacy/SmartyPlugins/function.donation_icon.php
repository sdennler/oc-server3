<?php
/**
 * Smarty function plugin: {donation_icon}
 *
 * Scans resource2/misc/donation/ for donations-N.{svg,png,gif,jpg} files,
 * picks one at random, and outputs an <img> with hover swap + preload.
 *
 * Assumes a matching donations-N-hover.EXT file always exists (same extension).
 *
 * Usage: {donation_icon}
 */
function smarty_function_donation_icon($params, &$smarty)
{
    $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/resource2/misc/donation';
    $baseUrl = '/resource2/misc/donation';

    if (!is_dir($baseDir)) {
        return '<!-- donation icon directory not found -->';
    }

    // Find all normal icons matching donations-N.ext (but not *-hover*)
    $files = glob($baseDir . '/donations-*');
    $variants = [];

    foreach ($files as $file) {
        $name = basename($file);
        if (preg_match('/^donations-(\d+)\.(svg|png|gif|jpg)$/', $name, $m)) {
            $variants[$m[1]] = $m[2];
        }
    }

    if (empty($variants)) {
        return '<!-- no donation icons found -->';
    }

    // Pick a random variant
    $keys = array_keys($variants);
    $picked = $keys[array_rand($keys)];
    $ext = $variants[$picked];

    $normalSrc = $baseUrl . '/donations-' . $picked . '.' . $ext;
    $hoverSrc  = $baseUrl . '/donations-' . $picked . '-hover.' . $ext;

    // Preload the hover image, then render the visible image with hover swap
    $html = '<link rel="preload" href="' . htmlspecialchars($hoverSrc) . '" as="image" />'
          . '<a href="articles.php?page=donations" style="display:inline-block;">'
          . '<img src="' . htmlspecialchars($normalSrc) . '" alt="Spenden" '
          . 'style="border:0; width:100px; height:100px;" '
          . 'onmouseover="this.src=\'' . htmlspecialchars($hoverSrc) . '\'" '
          . 'onmouseout="this.src=\'' . htmlspecialchars($normalSrc) . '\'" '
          . '/></a>';

    return $html;
}

