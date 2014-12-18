<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "Juno", "ServerBurstEvent~juno");
    public $name = "BURST~juno";
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

      $connection->setOption("rburststart", $command[0]);
    }

    public function receiveServerBurst($name, $connection) {
      $connection->setOption("lburstend", time());
      $burstLines = $connection->getOption("lburst");
      $connection->send(":".$this->juno->getSID()." BURST ".
        $connection->getOption("lburststart"));
      if (is_array($burstLines) && count($burstLines) > 0) {
        foreach ($burstLines as $line) {
          $connection->send($line);
        }
      }
      $connection->send(":".$this->juno->getSID()." ENDBURST ".
        $connection->getOption("lburstend"));
    }

    public function isInstantiated() {
      $this->juno = ModuleManagement::getModuleByName("Juno");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("burst", true, "juno"));
      EventHandling::registerForEvent("serverBurstEvent~juno", $this,
        "receiveServerBurst");
      return true;
    }
  }
?>
