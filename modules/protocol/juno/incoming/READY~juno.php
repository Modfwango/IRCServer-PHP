<?php
  class __CLASSNAME__ {
    public $depend = array("Outgoing~juno");
    public $name = "READY~juno";
    private $juno = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      if ($connection->getOption("sentburst") == false) {
        $this->juno->burst($connection);
      }
    }

    public function isInstantiated() {
      $this->juno = ModuleManagement::getModuleByName("Outgoing~juno");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("ready", true, "juno"));
      return true;
    }
  }
?>
