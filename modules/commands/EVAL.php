<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent");
    public $name = "EVAL";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if ($connection->getOption("registered") == true) {
        if ($connection->getOption("operator") == true) {
          $output = explode("\n", trim(eval(implode(" ", $command))));
          $i = 0;
          foreach ($output as $line) {
            $i++;
            $base = ":".__SERVERDOMAIN__." NOTICE ".
              $connection->getOption("nick")." :*** EVAL (".$i."):  ";
            $length = (510 - strlen($base));
            foreach (str_split($line, $length) as $outline) {
              $connection->send($base.$outline);
            }
          }
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 481 ".(
            $connection->getOption("nick") ? $connection->getOption("nick") :
            "*")." :Permission Denied - You're not an IRC operator");
        }
      }
      else {
        $connection->send(":".__SERVERDOMAIN__." 451 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." :You have not registered");
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "eval");
      return true;
    }
  }
?>
