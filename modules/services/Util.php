<?php
  class @@CLASSNAME@@ {
    public $depend = array();
    public $name = "Util";

    public function prettyStrChunk($string, $size, $ending) {
      $message = null;
      $line = array();
      $helptext = explode("\n", $string);
      foreach ($helptext as &$hline) {
        $hline = str_split(trim($hline), ($size - (strlen($ending) + 1)));
        foreach ($hline as $l) {
          $line[] = $l;
        }
        $line[] = null;
      }
      array_pop($helptext);
      foreach ($line as $k => $l) {
        if (strlen($l) == 1) {
          $message = substr($message, 0, (strlen($message) - (strlen(
            $ending) + 1))).$l."\r\n";
        }
        else {
          if ($k !== (count($line) - 1) && strlen($l) == ($size - (strlen(
              $ending) + 1)) && substr($l, -1) != " ") {
            $l .= "-";
          }
          $l .= "\r\n";
          if (strlen(trim($l)) > 0) {
            $message .= $l;
          }
          else {
            $message = substr($message, 0, (0 - (strlen($ending) + 1))).$ending;
          }
        }
      }
      return $message;
    }

    public function isInstantiated() {
      return true;
    }
  }
?>
