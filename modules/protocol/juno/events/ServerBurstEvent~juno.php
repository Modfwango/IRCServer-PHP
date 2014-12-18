<?php
  class __CLASSNAME__ {
    public $depend = array();
    public $name = "ServerBurstEvent~juno";

    public function isUnloadable() {
      return false;
    }

    public function receiveServerBurstEvent($name, $id, $connection) {
      if ($connection->getOption("lburstend") == false) {
        $connection->setOption("lburststart", time());
        $connection->setOption("lburst", array());
      }
      return array(false);
    }

    public function isInstantiated() {
      EventHandling::createEvent("serverBurstEvent~juno", $this);
      EventHandling::registerAsEventPreprocessor("serverBurstEvent~juno", $this,
        "receiveServerBurstEvent")
      return true;
    }
  }
?>
