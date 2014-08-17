<?php
  class @@CLASSNAME@@ {
    public $depend = array("Client", "CommandEvent", "Self");
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
              if (strlen(":".$this->self->getConfigFlag(
                  "serverdomain")." 303 ".$connection->getOption("nick")." :".
                  implode(" ", $online)) < 512) {
                $online[] = $c->getOption("nick");
              }
            }
          }

          while (strlen(":".$this->self->getConfigFlag("serverdomain")." 303 ".
                  $connection->getOption("nick")." :".implode(" ", $online))
                  > 512) {
            array_pop($online);
          }

          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." 303 ".$connection->getOption("nick")." :".
            implode(" ", $online));
        }
        else {
          $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 461 ".$connection->getOption("nick")." ISON :Not ".
          "enough parameters");
        }
      }
      else {
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 451 ".($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")." :You have not registered");
      }
    }

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "ison");
      return true;
    }
  }
?>
