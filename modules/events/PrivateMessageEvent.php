<?php
  class @@CLASSNAME@@ {
    public $name = "PrivateMessageEvent";

    public function isInstantiated() {
      EventHandling::createEvent("privateMessageEvent", $this);
      return true;
    }
  }
?>
