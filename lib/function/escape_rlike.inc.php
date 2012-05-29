<?php

function escape_rlike($string){
  $return_value = preg_replace("/([.\[\]*^\$])/", '\\\$1', $string);
  $return_value = str_replace('|', '\\\\|', $return_value);
  return $return_value;
}

?>