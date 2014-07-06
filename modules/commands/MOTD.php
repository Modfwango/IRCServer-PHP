<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent");
    public $name = "MOTD";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if (strtolower($command[0]) == "motd") {
        if ($connection->getOption("registered") == true) {
          if (is_string(__MOTD__)) {
            $connection->send(":".__SERVERDOMAIN__." 375 ".
              $connection->getOption("nick")." :- ".__SERVERDOMAIN__.
              " Message of the Day - ");
            if (stristr(__MOTD__, "\n")) {
              foreach (explode("\n", __MOTD__) as $line) {
                $line = str_split($line, 80);
                foreach ($line as $l) {
                  $connection->send(":".__SERVERDOMAIN__." 372 ".
                    $connection->getOption("nick")." :- ".$l);
                }
              }
            }
            else {
              $line = str_split(__MOTD__, 80);
              foreach ($line as $l) {
                $connection->send(":".__SERVERDOMAIN__." 372 ".
                  $connection->getOption("nick")." :- ".$l);
              }
            }
            $connection->send(":".__SERVERDOMAIN__." 376 ".
              $connection->getOption("nick")." :End of /MOTD command.");
          }
          else {
            $connection->send(":".__SERVERDOMAIN__." 422 ".
              $connection->getOption("nick")." :MOTD file is missing");
          }
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 451 ".(
            $connection->getOption("nick") ? $connection->getOption("nick") :
            "*")." :You have not registered");
        }
        return true;
      }
      return false;
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
