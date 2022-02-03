<?php

function bytelen($str) {
  if (defined('MB_OVERLOAD_STRING') && (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING) && extension_loaded('mbstring')) {
    return mb_strlen($str, '8bit');
  } else {
    return strlen($str);
  }
}

if (!is_callable('random_bytes')) {
  if (is_callable('gmp_random_bits')) {
    function random_bytes($b) {
      $rand = '';
      for ($i = 1; $i <= $b; $i++) {
        $rand .= chr(gmp_intval(gmp_random_bits(8)));
      }
      return ($rand !== false && bytelen($rand) == $b) ? $rand : null;
    }
  } else if (is_callable('openssl_random_pseudo_bytes')) {
    function random_bytes($b) {
      $rand = openssl_random_pseudo_bytes($b);
      return ($rand !== false && bytelen($rand) == $b) ? $rand : null;
    }
  } else if (is_callable('mcrypt_create_iv')) {
    if (DIRECTORY_SEPARATOR === '/') {
      function random_bytes($b) {
        $rand = mcrypt_create_iv($b, MCRYPT_DEV_URANDOM);
        return ($rand !== false && bytelen($rand) == $b) ? $rand : null;
      }
    } else {
      function random_bytes($b) {
        $rand = mcrypt_create_iv($b);
        return ($rand !== false && bytelen($rand) == $b) ? $rand : null;
      }
    }
  } else if (DIRECTORY_SEPARATOR === '/') {
    $stat = @stat('/dev/urandom');
    if ($stat !== false && ($stat['mode'] & 0170000) === 020000) {
      function random_bytes($b) {
        $rand = @file_get_contents('/dev/urandom', false, null, 0, $b);
        return ($rand !== false && bytelen($rand) == $b) ? $rand : null;
      }
    }
  } elseif (class_exists('\\COM')) {
    try {
      $util = new COM('CAPICOM.Utilities.1');
      $method = array($util, 'GetRandom');
      if (is_callable($method)) {
        function random_bytes($b) {
          $util = new \COM('CAPICOM.Utilities.1');
          $rand = base64_decode($util->GetRandom($b,0));
          $rand = str_pad($rand, $b, chr(0));
          return ($rand !== false && bytelen($rand) == $b) ? $rand : null;
        }
      }
    } catch (Exception $e) { }
  }

  if (!is_callable('random_bytes')) {
    if (is_callable('mt_rand')) {
      function random_bytes($b) {
        $rand = '';
        for ($i = 1; $i <= $b; $i++) {
          $rand .= chr(mt_rand(0,255));
        }
        return ($rand !== false && bytelen($rand) == $b) ? $rand : null;
      }
    } else {
      function random_bytes($b) {
        $rand = '';
        for ($i = 1; $i <= $b; $i++) {
          $rand .= chr(rand(0,255));
        }
        return ($rand !== false && bytelen($rand) == $b) ? $rand : null;
      }
    }
  }
}

?>
