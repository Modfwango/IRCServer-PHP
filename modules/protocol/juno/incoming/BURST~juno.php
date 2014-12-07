<?php
  class __CLASSNAME__ {
    public $name = "BURST~juno";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);
      $source = array_shift($command);

      $connection->setOption("startburst", $command[0]);
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("burst", true, "juno"));
      return true;
    }
  }
?>
