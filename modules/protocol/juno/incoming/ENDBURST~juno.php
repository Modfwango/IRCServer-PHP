<?php
  class __CLASSNAME__ {
    public $depend = array("Outgoing~juno");
    public $name = "ENDBURST~juno";
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

      $connection->setOption("endburst", $command[0]);
      if ($connection->getOption("sentburst") == false) {
        $this->juno->burst($connection);
      }
    }

    public function isInstantiated() {
      $this->juno = ModuleManagement::getModuleByName("Outgoing~juno");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("endburst", true, "juno"));
      return true;
    }
  }
?>
