<?php
  class __CLASSNAME__ {
    public $depend = array("Juno");
    public $name = "PASS~juno";
    private $juno = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if ($connection->getOption("recvpass") == $command[0]) {
        $connection->setOption("rauthenticated", true);
        $event = EventHandling::getEventByName("serverAuthenticatedEvent~juno");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            // Trigger the serverAuthenticatedEvent~juno event for each
            // registered module
            EventHandling::triggerEvent("serverAuthenticatedEvent~juno", $id,
              $connection);
          }
        }
      }
      else {
        $connection->disconnect();
      }
    }

    public function receiveServerEvent($name, $connection) {
      if ($connection->getOption("lacquainted") == true &&
          $connection->getOption("racquainted") == true &&
          $connection->getOption("lauthenticated") == false) {
        $connection->send("PASS ".$connection->getOption("sendpass"));
        $connection->setOption("lauthenticated", true);

        $event = EventHandling::getEventByName("serverAuthenticatedEvent~juno");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            // Trigger the serverAuthenticatedEvent~juno event for each
            // registered module
            EventHandling::triggerEvent("serverAuthenticatedEvent~juno", $id,
              $connection);
          }
        }
      }
    }

    public function isInstantiated() {
      $this->juno = ModuleManagement::getModuleByName("Juno");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("pass", true, "juno"));
      EventHandling::registerForEvent("serverAcquaintedEvent~juno", $this,
        "receiveServerEvent");
      EventHandling::registerForEvent("serverAuthenticatedEvent~juno", $this,
        "receiveServerEvent");
      return true;
    }
  }
?>
