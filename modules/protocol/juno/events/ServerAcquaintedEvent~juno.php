<?php
  class __CLASSNAME__ {
    public $depend = array();
    public $name = "ServerAcquaintedEvent~juno";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("serverAcquaintedEvent~juno", $this);
      return true;
    }
  }
?>
