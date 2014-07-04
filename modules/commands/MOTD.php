<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent");
    public $name = "MOTD";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if (strtolower($command[0]) == "motd") {
        if (is_string(__MOTD__) && stristr(__MOTD__, "\n")) {
          $connection->send(":__SERVERDOMAIN__ 375 ".(
            $connection->getOption("nick") ? $connection->getOption("nick") :
            "*")." :- ".__SERVERDOMAIN__." Message of the Day - ");
          foreach (explode("\n", __MOTD__) as $line) {
            $connection->send(":__SERVERDOMAIN__ 372 ".(
              $connection->getOption("nick") ? $connection->getOption("nick") :
              "*")." :- ".$line);
          }
          $connection->send(":__SERVERDOMAIN__ 376 ".(
            $connection->getOption("nick") ? $connection->getOption("nick") :
            "*")." :End of /MOTD command.");
        }
        else {
          $connection->send(":__SERVERDOMAIN__ 422 ".(
            $connection->getOption("nick") ? $connection->getOption("nick") :
            "*")." :MOTD file is missing");
        }
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
