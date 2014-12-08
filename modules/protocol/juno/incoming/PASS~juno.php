<?php
  class __CLASSNAME__ {
    public $depend = array("Outgoing~juno");
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
        $connection->setOption("authenticated", true);
        if ($connection->getOption("sentpass") == false) {
          $this->juno->authenticate($connection);
        }
      }
      else {
        $connection->disconnect();
      }
    }

    public function isInstantiated() {
      $this->juno = ModuleManagement::getModuleByName("Outgoing~juno");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("pass", true, "juno"));
      return true;
    }
  }
?>
