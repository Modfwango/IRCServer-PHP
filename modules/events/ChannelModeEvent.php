<?php
  class @@CLASSNAME@@ {
    public $name = "ChannelModeEvent";

    public function isInstantiated() {
      EventHandling::createEvent("channelModeEvent", $this);
      return true;
    }
  }
?>
