<?php
  class @@CLASSNAME@@ {
    public $depend = array("Client", "CommandEvent");
    public $name = "ISON";
    private $client = null;

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
          $online = array();
          foreach ($command as $user) {
            $c = $this->client->getClientByNick($user);
            if ($c != false) {
              if (strlen(":".__SERVERDOMAIN__." 303 ".
                  $connection->getOption("nick")." :".implode(" ",
                  $online)) < 512) {
                $online[] = $c->getOption("nick");
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
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 461 ".
            $connection->getOption("nick")." ISON :Not enough parameters");
        }
      }
      else {
        $connection->send(":".__SERVERDOMAIN__." 451 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." :You have not registered");
      }
    }

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "ison");
      return true;
    }
  }
?>
