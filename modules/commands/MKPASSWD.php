<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent");
    public $name = "MKPASSWD";

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
        if (count($command) > 0) {
          $connection->send(":".__SERVERDOMAIN__." NOTICE ".
            $connection->getOption("nick")." :*** Authentication phrase is: ".
            password_hash($command[0], PASSWORD_DEFAULT));
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 461 ".
            $connection->getOption("nick")." MKPASSWD :Not enough parameters");
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
        "mkpasswd");
      return true;
    }
  }
?>
