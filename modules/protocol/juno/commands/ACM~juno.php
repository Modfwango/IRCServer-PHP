<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "Juno", "Modes",
      "ServerBurstEvent~juno");
    public $name = "ACM~juno";
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
          $config = $this->juno->getConnection(
            $connection->getOption("servhost"));
          $map = (isset($config["forwardmodemap"]["channel"][$item[0]]) ?
            $config["forwardmodemap"]["channel"][$item[0]] : false);
          $mode = $this->modes->getModeByName(($map != false ? $map :
            $item[0]));
          if ($mode != false) {
            $this->modes->setMode(array($mode[0], $item[1], $mode[2], $mode[3],
              $mode[4], $mode[5]), $connection->getOption("alphabet"));
          }
        }
      }
    }

    public function receiveServerBurst($name, $id, $connection) {
      $config = $this->juno->getConnection($connection->getOption("servhost"));
      $lburst = $connection->getOption("lburst");
      $modes = array();
      foreach ($this->modes->getModesByTarget("0") as $mode) {
        $name = (isset($config["reversemodemap"]["channel"][$mode[0]]) ?
          $config["reversemodemap"]["channel"][$mode[0]] : false);
        if ($name != false) {
          $modes[] = $name.":".$mode[1].":".$mode[3];
        }
      }
      if (count($modes) > 0) {
        $lburst[] = ":".$this->juno->getSID()." ACM ".implode(" ", $modes);
      }
      $connection->setOption("lburst", $lburst);
      return array(true);
    }

    public function isInstantiated() {
      $this->juno = ModuleManagement::getModuleByName("Juno");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("acm", true, "juno"));
      EventHandling::registerAsEventPreprocessor("serverBurstEvent~juno", $this,
        "receiveServerBurst");
      return true;
    }
  }
?>
