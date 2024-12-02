<?php
/**
 * Bezpečné escapování HTML
 */
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formátování čísla na české koruny
 */
function formatMoney($amount) {
    return number_format($amount, 0, ',', ' ') . ' Kč';
}

/**
 * Formátování data do českého formátu
 */
function formatDate($date) {
    return date('d.m.Y', strtotime($date));
}

/**
 * Formátování data a času do českého formátu
 */
function formatDateTime($date) {
    return date('d.m.Y H:i', strtotime($date));
}
?> 