<?php

/* Reverses html entities encoding. */

function html_decode($encoded) {
    return strtr($encoded,array_flip(get_html_translation_table(HTML_ENTITIES)));
}

?>