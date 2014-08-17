<?php
  class __CLASSNAME__ {
    public $depend = array("Client", "CommandEvent", "NickChangeEvent", "Self",
      "UserRegistrationEvent");
    public $name = "NICK";
    private $client = null;
    private $self = null;

    private function nicknameAvailable($nick) {
      if ($this->client->getClientByNick($nick) == true) {
        return false;
      }

      foreach (ConnectionManagement::getConnections() as $connection) {
        if (strtolower($connection->getOption("nick")) == strtolower($nick)) {
          return false;
        }
      }
      return true;
    }

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if (preg_match("/^[[\\]a-zA-Z\\\\`_^{|}][[\\]a-zA-Z0-9\\\\`_^{|}-]*$/",
          $command[0]) && count($command) == 1) {
        if ($this->nicknameAvailable(substr($command[0], 0, 30)) != false) {
          $oldnick = $connection->getOption("nick");
          $connection->setOption("nick", substr($command[0], 0, 30));
          if ($connection->getOption("registered") == false) {
            if ($connection->getOption("ident") != false) {
              $connection->setOption("registered", true);
              $event = EventHandling::getEventByName("userRegistrationEvent");
              if ($event != false) {
                foreach ($event[2] as $id => $registration) {
                  // Trigger the userRegistrationEvent event for each
                  // registered module.
                  EventHandling::triggerEvent("userRegistrationEvent", $id,
                      $connection);
                }
              }
            }
          }
          else {
            $event = EventHandling::getEventByName("nickChangeEvent");
            if ($event != false) {
              foreach ($event[2] as $id => $registration) {
                // Trigger the nickChangeEvent event for each registered
                // module.
                EventHandling::triggerEvent("nickChangeEvent", $id,
                    array($connection, $oldnick));
              }
            }
          }
        }
        else {
          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." 433 ".($connection->getOption("nick") ?
            $connection->getOption("nick") : "*")." ".$command[0]." :Nickname ".
            "is already in use.");
        }
      }
      elseif (count($command) > 0) {
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 432 * ".$command[0]." :Erroneous Nickname");
      }
      else {
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 431 ".($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")." :No nickname given");
      }
    }

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "nick");
      return true;
    }
  }
?>
