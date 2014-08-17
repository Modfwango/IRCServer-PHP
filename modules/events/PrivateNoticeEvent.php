<?php
  class @@CLASSNAME@@ {
    public $name = "PrivateNoticeEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("privateNoticeEvent", $this);
      return true;
    }
  }
?>
