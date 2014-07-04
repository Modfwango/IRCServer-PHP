<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent");
    public $name = "MOTD";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if ($connection->getOption("registered") == true
           && strtolower($command[0]) == "motd") {
        if (is_string(__MOTD__)) {
          $connection->send(":".__SERVERDOMAIN__." 375 ".
            $connection->getOption("nick")." :- ".__SERVERDOMAIN__.
            " Message of the Day - ");
          if (stristr(__MOTD__, "\n")) {
            foreach (explode("\n", __MOTD__) as $line) {
              $connection->send(":".__SERVERDOMAIN__." 372 ".
                $connection->getOption("nick")." :- ".$line);
            }
          }
          else {
            $connection->send(":".__SERVERDOMAIN__." 372 ".
              $connection->getOption("nick")." :- ".__MOTD__);
          }
          $connection->send(":".__SERVERDOMAIN__." 376 ".
            $connection->getOption("nick")." :End of /MOTD command.");
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 422 ".
            $connection->getOption("nick")." :MOTD file is missing");
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
