<?php
  class @@CLASSNAME@@ {
    public $name = "Util";

    public function genUUID() {
      return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff),
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff));
    }

    public function getStringsWithBaseAndMaxLengthAndObjects($base, $objects,
        $includeFirstSpace = true, $maxLength = 0, $maxObjects = 0) {
      $ret = array();
      if (count($objects) > 0) {
        foreach ($objects as $key => $object) {
          $objects[$key] = " ".$object;
        }
        while (count($objects) > 0) {
          $objCount = 0;
          $string = $base;
          if ($includeFirstSpace == false) {
            $objects[0] = substr($objects[0], 1);
          }
          while (true) {
            if (count($objects) == 0
                || ($maxLength > 0
                && strlen($string.$objects[0]) >= $maxLength)
                || ($maxObjects > 0 && $objCount > $maxObjects)) {
              break;
            }
            $string .= array_shift($objects);
            $objCount++;
          }
          $ret[] = $string;
        }
      }
      return $ret;
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

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      return true;
    }
  }
?>
