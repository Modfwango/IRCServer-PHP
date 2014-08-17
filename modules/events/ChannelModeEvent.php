<?php
  class @@CLASSNAME@@ {
    public $name = "ChannelModeEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("channelModeEvent", $this);
      return true;
    }
  }
?>
