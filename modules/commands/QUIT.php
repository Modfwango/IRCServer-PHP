<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent", "UserQuitEvent");
    public $name = "MOTD";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if (strtolower($command[0]) == "quit") {
        $message = "Client Quit";
        if (count($command) > 1) {
          $message = "Quit: ".$command[1];
        }

        if ($connection->getOption("registered") == true) {
          $connection->send(":".$connection->getOption("nick")."!".
            $connection->getOption("ident")."@".$connection->getHost().
            " QUIT :".$message);
        }
        $connection->send("ERROR :Closing Link: ".$connection->getHost()." (".
          $message.")");
        $connection->disconnect();
        if ($connection->getOption("registered") == true) {
          $event = EventHandling::getEventByName("userQuitEvent");
          if ($event != false) {
            foreach ($event[2] as $id => $registration) {
              // Trigger the userQuitEvent event for each
              // registered module.
              EventHandling::triggerEvent("userQuitEvent", $id,
                  array($connection, $message));
            }
          }
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
