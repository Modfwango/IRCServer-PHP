<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "ConnectionDisconnectedEvent",
      "UserQuitEvent");
    public $name = "QUIT";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      $message = "Client Quit";
      if (count($command) > 0) {
        $message = "Quit: ".$command[0];
      }

      if ($connection->getOption("registered") == true) {
        $connection->send(":".$connection->getOption("nick")."!".
          $connection->getOption("ident")."@".$connection->getHost().
          " QUIT :".$message);
      }
      $connection->send("ERROR :Closing Link: ".$connection->getHost()." (".
        $message.")");
      $this->notifyQuit(null, $connection, $message);
      $connection->setOption("registered", false);
      $connection->disconnect();
    }

    public function notifyQuit($name, $connection, $message = null) {
      if ($connection->getOption("registered") == true) {
        $event = EventHandling::getEventByName("userQuitEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            // Trigger the userQuitEvent event for each
            // registered module.
            EventHandling::triggerEvent("userQuitEvent", $id,
                array($connection, ($message != null ? $message :
                "Remote host closed the connection")));
          }
        }
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "quit");
      EventHandling::registerForEvent("connectionDisconnectedEvent", $this,
        "notifyQuit");
      return true;
    }
  }
?>
