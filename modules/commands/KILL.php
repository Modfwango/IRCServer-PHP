<?php
  class @@CLASSNAME@@ {
    public $depend = array("Client", "CommandEvent", "QUIT", "Self");
    public $name = "KILL";

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
        if ($connection->getOption("operator") == true) {
          if (count($command) > 0) {
            $nick = array_shift($command);
            $client = $this->client->getClientByNick($nick);
            if ($client != false) {
              $message = "Killed";
              if (count($command) > 0) {
                $message = "Killed: ".implode(" ", $command);
              }
              $client->send("ERROR :Closing Link: ".$client->getHost()." (".
                $message.")");
              ModuleManagement::getModuleByName("QUIT")->notifyQuit(null,
                $client, $message);
              $client->setOption("registered", false);
              $client->disconnect();
            }
            else {
              $connection->send(":".$this->self->getConfigFlag(
                "serverdomain")." 401 ".$connection->getOption("nick")." ".
                $nick." :No such nick/channel");
            }
          }
          else {
            $connection->send(":".$this->self->getConfigFlag(
              "serverdomain")." 461 ".$connection->getOption("nick")." KILL ".
              ":Not enough parameters");
          }
        }
        else {
          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." 481 ".($connection->getOption("nick") ?
            $connection->getOption("nick") : "*")." :Permission Denied - ".
            "You're not an IRC operator");
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
        "kill");
      return true;
    }
  }
?>
