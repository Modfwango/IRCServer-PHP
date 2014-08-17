<?php
  class @@CLASSNAME@@ {
    public $name = "ShouldExposeChannelToUserEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("shouldExposeChannelToUserEvent", $this);
      return true;
    }
  }
?>
