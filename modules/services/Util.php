<?php
  class @@CLASSNAME@@ {
    public $depend = array();
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

    public function isInstantiated() {
      return true;
    }
  }
?>
