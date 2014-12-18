<?php
  class __CLASSNAME__ {
    public $depend = array("Juno");
    public $name = "READY~juno";
    private $juno = null;
    private $shouldReady = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $event = EventHandling::getEventByName("serverBurstEvent~juno");
      if ($event != false) {
        foreach ($event[2] as $id => $registration) {
          // Trigger the serverBurstEvent~juno event for each
          // registered module
          EventHandling::triggerEvent("serverBurstEvent~juno", $id,
            $connection);
        }
      }
    }

    public function receiveServerAuthenticatedEvent($name, $connection) {
      if ($this->shouldReady == true) {
        $connection->send("READY");
      }
      if ($connection->getOption("lauthenticated") == false &&
          $connection->getOption("rauthenticated") == true) {
        $this->shouldReady = true;
      }
    }

    public function isInstantiated() {
      $this->juno = ModuleManagement::getModuleByName("Juno");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("ready", true, "juno"));
      EventHandling::registerForEvent("serverAuthenticatedEvent~juno", $this,
        "receiveServerAuthenticatedEvent");
      return true;
    }
  }
?>
