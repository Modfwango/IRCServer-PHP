<?php
  class @@CLASSNAME@@ {
    public $name = "ChannelPartEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("channelPartEvent", $this);
      return true;
    }
  }
?>
