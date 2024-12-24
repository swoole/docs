<?php
$publicDir = dirname(__DIR__) . '/public';
$navbarFile = $publicDir . '/zh-cn/_navbar.md';
$navbar = file_get_contents($navbarFile);
preg_match_all('/\[(.+)]\((.+)\)/', $navbar, $matches);

foreach ($matches[2] as $key => $url) {
    $title = $matches[1][$key];
    $url = str_replace('https://wiki.swoole.com/', '', $url);
    $lang = rtrim($url, '/');
    ob_start();
    include __DIR__ . '/index-tpl.php';
    $html = ob_get_clean();
    file_put_contents($publicDir . '/' . $lang . '/index.html', $html);
    copy($navbarFile, $publicDir . '/' . $lang . '/_navbar.md');
}