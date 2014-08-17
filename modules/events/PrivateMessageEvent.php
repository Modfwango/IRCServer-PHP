<?php
  class @@CLASSNAME@@ {
    public $name = "PrivateMessageEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("privateMessageEvent", $this);
      return true;
    }
  }
?>
