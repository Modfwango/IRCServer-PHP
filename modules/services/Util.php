<?php
  class @@CLASSNAME@@ {
    public $depend = array();
    public $name = "Util";

    public function prettyStrChunk($string, $size, $ending) {
      $ret = array();
      $string = explode(" ", $string);
      $line = 0;
      while (count($string) > 0) {
        $line++;
        $lastCount = count($string);
        while ((strlen($ret[$line]) + (strlen($string) +
                (strlen($ending) + 2))) < ($size + 1)) {
          $ret[$line] .= " ".array_shift($string);
          $ret[$line] = trim($ret[$line]);
        }
        if (count($string) == $lastCount) {
          if (strlen($string[0]) > ($size - strlen($ending))) {
            $string = chunk_split(array_shift($string),
              ($size - (strlen($ending) + 1)), "-".$ending);
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
