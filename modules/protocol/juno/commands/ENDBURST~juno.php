<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "ServerBurstEvent~juno");
    public $name = "ENDBURST~juno";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      $connection->setOption("rburstend", $command[0]);
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

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("endburst", true, "juno"));
      return true;
    }
  }
?>
