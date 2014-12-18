<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "Juno", "Modes",
      "ServerBurstEvent~juno");
    public $name = "AUM~juno";
    private $juno = null;
    private $modes = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if (count($command) > 0) {
        foreach ($command as $item) {
          $item = explode(":", $item);
          $this->modes->setMode(array($item[0], $item[1], "1", $item[2]),
            $connection->getOption("alphabet"));
        }
      }
    }

    public function receiveServerBurst($name, $id, $connection) {
      $lburst = $connection->getOption("lburst");
      $modes = array();
      foreach ($this->modes->getModesByTarget("1") as $mode) {
        $modes[] = $mode[0].":".$mode[1];
      }
      $lburst[] = ":".$this->juno->getSID()." AUM ".implode(" ", $modes);
      $connection->setOption("lburst", $lburst);
      return array(true);
    }

    public function isInstantiated() {
      $this->juno = ModuleManagement::getModuleByName("Juno");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("aum", true, "juno"));
      EventHandling::registerAsEventPreprocessor("serverBurstEvent~juno", $this,
        "receiveServerBurst");
      return true;
    }
  }
?>
