<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent");
    public $name = "PRIVMSG";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if ($connection->getOption("registered") == true
           && strtolower($command[0]) == "privmsg") {
        if (count($command) == 3) {
          foreach (ConnectionManagement::getConnections() as $c) {
            $nick = $c->getOption("nick");
            if ($nick != false && strtolower($nick)
                == strtolower($command[1])) {
              $c->send(":".$connection->getOption("nick")."!".
                $connection->getOption("ident")."@".$connection->getHost().
                " PRIVMSG ".$nick." :".$command[2]);
              return true;
            }
          }
          $connection->send();
        }
        elseif (count($command) == 2) {
          $connection->send(":".__SERVERDOMAIN__." 412 ".
            $connection->getOption("nick")." :No text to send");
        }
        elseif (count($command) == 1) {
          $connection->send(":".__SERVERDOMAIN__." 411 ".
            $connection->getOption("nick")." :No recipient given (PRIVMSG)");
        }
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
