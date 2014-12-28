<?php
  class __CLASSNAME__ {
    public $depend = array("Client", "CommandEvent", "UserModeEvent",
      "UserRegistrationEvent");
    public $name = "UID~juno";
    private $client = null;
    private $modes = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];
      $source = array_shift($command);

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if (count($command) == 9) {
        // Create a stub Connection
        $c = new Connection("0", array($command[5], 0, false, array(
          "id" => $command[0],
          "ident" => $command[4],
          "idle" => $command[1],
          "nick" => $command[3],
          "nickts" => $command[1],
          "realname" => $command[8],
          "registered" => true,
          "server" => $source,
          "signon" => $command[1]
        )));

        // Add Connection stub to Client database
        $this->client->setClient($c);

        // Parse modes
        $modes = $this->modes->parseModes("1", $command[2],
          $connection->getOption("alphabet")); // Switch to dynamic SID alphabet
        $event = EventHandling::getEventByName("userModeEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            // Trigger the userModeEvent event for each registered
            // module.
            EventHandling::triggerEvent("userModeEvent", $id, array($c,
              $modes));
          }
        }
      }
    }

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("uid", true, "juno"));
      EventHandling::registerForEvent("userRegistrationEvent", $this,
        "receiveUserRegistration");
      return true;
    }
  }
?>
