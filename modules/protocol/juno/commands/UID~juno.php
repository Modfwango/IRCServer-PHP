<?php
  class __CLASSNAME__ {
    public $depend = array("Client", "CommandEvent", "Juno", "Server",
      "UserModeEvent", "UserRegistrationEvent");
    public $name = "UID~juno";
    private $client = null;
    private $juno = null;
    private $modes = null;
    private $server = null;

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
          $this->server->getServerBySID($source)->getOption("alphabet"));
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

    public function receiveServerBurst($name, $id, $connection) {
      $lburst = $connection->getOption("lburst");
      foreach ($this->client->getClients() as $client) {
        $components = $this->modes->getModeStringComponents(
          $client->getOption("modes"), true, array(),
          $connection->getOption("alphabet"));
        $lburst[] = ":".$this->juno->getSID()." UID ".
          $client->getOption("id")." ".$client->getOption("nickts")." +".
          trim(implode(" ", array(implode($components[0]), implode(
          $components[1]))))." ".$client->getOption("nick")." ".
          $client->getOption("ident")." ".$client->getHost()." ".
          $client->getHost()." ".$client->getIP()." ".
          $client->getOption("realname");
      }
      $connection->setOption("lburst", $lburst);
      return array(true);
    }

    public function receiveUserRegistration($name, $connection) {
      $static = array(
        $connection->getOption("id"),
        $connection->getOption("nickts"),
        null,
        $connection->getOption("nick"),
        $connection->getOption("ident"),
        $connection->getHost(),
        $connection->getHost(),
        $connection->getIP(),
        $connection->getOption("realname")
      );
      foreach ($this->server->getServersByProtocol("juno") as $server) {
        $components = $this->modes->getModeStringComponents(
          (is_array($connection->getOption("modes")) ?
          $connection->getOption("modes") : array()), true, array(),
          $server->getOption("alphabet"));
        $static[2] = trim("+".implode($components[0])." ".
          implode($components[1]));
        $server->send(":".$this->juno->getSID()." UID ".implode(" ", $static));
      }
    }

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->juno = ModuleManagement::getModuleByName("Juno");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->server = ModuleManagement::getModuleByName("Server");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("uid", true, "juno"));
      EventHandling::registerForEvent("userRegistrationEvent", $this,
        "receiveUserRegistration");
      return true;
    }
  }
?>
