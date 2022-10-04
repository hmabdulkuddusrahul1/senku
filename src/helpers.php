<?php 

namespace Mateodioev\Senku;

use function str_replace;

/**
 * Remove html entities
 */
function xQuit(?string $message=''): string {
  return str_replace(
    ['<', '>', '≤', '≥'],
    ['&lt;', '&gt;', '&le;', '&ge;'],
    $message ?? ''
  );
}

function i(string $str): string {
  return '<i>' . $str . '</i>';
}

function code(string $str): string {
  return '<code>' . $str . '</code>';
}

function b(string $str): string {
  return '<b>' . $str . '</b>';
}

function u(string $str): string {
  return '<u>' . $str . '</u>';
}

function n(): string {
  return PHP_EOL;
}

function a(string $link, string $label) {
  return '<a href="' . $link . '">' . $label . '</a>';
}
