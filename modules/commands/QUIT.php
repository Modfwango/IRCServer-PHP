<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent");
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
