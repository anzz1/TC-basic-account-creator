<?php

  // explicitly set charset to utf-8
  ini_set('default_charset', 'utf-8');
  ini_set('input_encoding', 'utf-8');
  ini_set('output_encoding', 'utf-8');
  ini_set('internal_encoding', 'utf-8');
  if(ini_get('mbstring.func_overload')) {
    ini_set('mbstring.func_overload', 0);
  }
  if(ini_get('mbstring.detect_order')) {
    ini_set('mbstring.detect_order', 'pass');
  }
  if(ini_get('mbstring.http_input')) {
    ini_set('mbstring.http_input', 'pass');
  }
  if(ini_get('mbstring.http_output')) {
    ini_set('mbstring.http_output', 'pass');
  }
  if(ini_get('mbstring.internal_encoding')) {
    ini_set('mbstring.internal_encoding', '');
  }
  if(ini_get('mbstring.encoding_translation')) {
    ini_set('mbstring.encoding_translation', 0);
  }
  if(ini_get('iconv.input_encoding')) {
    ini_set('iconv.input_encoding', '');
  }
  if(ini_get('iconv.output_encoding')) {
    ini_set('iconv.input_encoding', '');
  }
  if(ini_get('iconv.internal_encoding')) {
    ini_set('iconv.internal_encoding', '');
  }

?>
