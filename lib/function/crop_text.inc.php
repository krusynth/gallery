<?php

function crop_text($string, $length, $ellipsis = '') {
  if(strlen($string) > $length) {
    $string = substr($string, 0, $length).$ellipsis;
  }
  return $string;
}

?>