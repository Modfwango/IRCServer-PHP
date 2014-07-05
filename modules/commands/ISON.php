<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent");
    public $name = "ISON";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if ($connection->getOption("registered") == true
           && strtolower($command[0]) == "ison") {
        if (count($command) > 1) {
          unset($command[0]);
          $online = array();
          foreach ($command as $user) {
            foreach (ConnectionManagement::getConnections() as $c) {
              if (strtolower($c->getOption("nick")) == strtolower($user)) {
                if (strlen(":".__SERVERDOMAIN__." 303 ".
                        $connection->getOption("nick")." :".implode(" ", $online))
                        < 512) {
                  $online[] = $c->getOption("nick");
                }
              }
            }
          }

          while (strlen(":".__SERVERDOMAIN__." 303 ".
                  $connection->getOption("nick")." :".implode(" ", $online))
                  > 512) {
            array_pop($online);
          }

          $connection->send(":".__SERVERDOMAIN__." 303 ".
            $connection->getOption("nick")." :".implode(" ", $online));
          return true;
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 461 ".
            $connection->getOption("nick")." ISON :Not enough parameters");
        }
      }
      return false;
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
