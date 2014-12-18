<?php
  class __CLASSNAME__ {
    public $depend = array();
    public $name = "ServerAuthenticatedEvent~juno";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("serverAuthenticatedEvent~juno", $this);
      return true;
    }
  }
?>
