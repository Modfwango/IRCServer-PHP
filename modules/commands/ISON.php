<?php
  class @@CLASSNAME@@ {
    public $depend = array("Client", "CommandEvent");
    public $name = "ISON";
    private $client = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if (strtolower($command[0]) == "ison") {
        if ($connection->getOption("registered") == true) {
          if (count($command) > 1) {
            unset($command[0]);
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
        return true;
      }
      return false;
    }

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
