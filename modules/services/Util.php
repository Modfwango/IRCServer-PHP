<?php
  class @@CLASSNAME@@ {
    public $name = "Util";

    public function prettyStrChunk($string, $size, $ending) {
      $ret = array();
      $s = explode("\n", $string);
      foreach ($s as &$stringf) {
        $stringf = explode(" ", trim($stringf));
      }
      $line = 0;
      foreach ($s as $string) {
        while (count($string) > 0) {
          $ret[$line] = null;
          $lastCount = count($string);
          while (isset($string[0]) && (strlen($ret[$line]) +
                  (strlen($string[0]) + (strlen($ending) + 2))) <= $size) {
            $ret[$line] .= " ".array_shift($string);
            $ret[$line] = trim($ret[$line]);
          }
          $line++;
          if (count($string) == $lastCount) {
            if (strlen($string[0]) > ($size - strlen($ending))) {
              $str = chunk_split(array_shift($string),
                ($size - (strlen($ending) + 1)), "-".$ending);
              foreach ($str as $substr) {
                array_unshift($string, $substr);
              }
            }
          }
        }
      }
      return implode("\r\n", $ret);
    }

    public function validateEmail($email) {
      $atIndex = strrpos($email, "@");
      if (is_bool($atIndex) && !$atIndex) {
        return false;
      }
      else {
        $domain = substr($email, $atIndex + 1);
        $local = substr($email, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);

        if ($localLen < 1 || $localLen > 64) {
          return false;
        }
        else if ($domainLen < 1 || $domainLen > 255) {
          return false;
        }
        else if ($local[0] == '.' || $local[$localLen - 1] == '.'
                  || $domain[0] == '.' || $domain[$domainLen - 1] == '.') {
          return false;
        }
        else if (preg_match('/\\.\\./', $local)) {
          return false;
        }
        else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
          return false;
        }
        else if (preg_match('/\\.\\./', $domain)) {
          return false;
        }
        else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                  str_replace("\\\\", null, $local))) {
          if (!preg_match('/^"(\\\\"|[^"])+"$/',
              str_replace("\\\\", null, $local))) {
            return false;
          }
        }
        if (!(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
          return false;
        }
      }
      return true;
    }

    public function isInstantiated() {
      return true;
    }
  }
?>
