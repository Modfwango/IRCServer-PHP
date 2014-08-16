<?php
  class @@CLASSNAME@@ {
    public $name = "ShouldExposeChannelToUserEvent";

    public function isInstantiated() {
      EventHandling::createEvent("shouldExposeChannelToUserEvent", $this);
      return true;
    }
  }
?>
