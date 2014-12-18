<?php
  class __CLASSNAME__ {
    public $depend = array("Juno", "Self"/*, "Server"*/);
    public $name = "SERVER~juno";
    private $juno = null;
    private $self = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      $c = $this->juno->getConnection($command[1]);
      if ($this->juno->getVersion() == $command[2] && $c != false) {
        if (/*$this->server->getServerBySID($command[0]) == false &&
            $this->server->getServerByHost($command[1]) == false &&*/
            $this->juno->getSID() != $command[0] &&
            strtolower($this->self->getConfigFlag("serverdomain")) !=
            strtolower($command[1])) {
          $connection->setOption("alphabet", $this->modes->createAlphabet());
          $connection->setOption("server", true);
          $connection->setOption("protocol", "juno");
          $connection->setOption("sid", $command[0]);
          $connection->setOption("servhost", $command[1]);
          $connection->setOption("sendpass", $c["sendpass"]);
          $connection->setOption("recvpass", $c["recvpass"]);
          $connection->setOption("raquainted", true);

          $event = EventHandling::getEventByName("serverAcquaintedEvent~juno");
          if ($event != false) {
            foreach ($event[2] as $id => $registration) {
              // Trigger the serverAcquaintedEvent~juno event for each
              // registered module
              EventHandling::triggerEvent("serverAcquaintedEvent~juno", $id,
                $connection);
            }
          }
        }
        else {
          $connection->disconnect();
        }
      }
    }

    public function receiveServerEvent($name, $connection) {
      if ($connection->getOption("lacquainted") == false &&
          $connection->getOption("protocol") == "juno" &&
          $connection->getOption("server") == true) {
        $connection->send("SERVER ".$this->config["sid"]." ".
          $this->self->getConfigFlag("serverdomain")." ".
          $this->juno->getVersion()." ".
          $this->self->getConfigFlag("version")." :".
          $this->self->getConfigFlag("serverdescription"));
        $connection->setOption("lacquainted", true);

        $event = EventHandling::getEventByName("serverAcquaintedEvent~juno");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            // Trigger the serverAcquaintedEvent~juno event for each
            // registered module
            EventHandling::triggerEvent("serverAcquaintedEvent~juno", $id,
              $connection);
          }
        }
      }
    }

    public function isInstantiated() {
      $this->juno = ModuleManagement::getModuleByName("Juno");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("server"));
      EventHandling::registerForEvent("connectionConnectedEvent", $this,
        "receiveConnectionConnected");
      EventHandling::registerForEvent("serverAcquaintedEvent~juno", $this,
        "receiveServerEvent");
      return true;
    }
  }
?>
