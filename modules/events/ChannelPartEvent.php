<?php
  class @@CLASSNAME@@ {
    public $name = "ChannelPartEvent";

    public function isInstantiated() {
      EventHandling::createEvent("channelPartEvent", $this);
      return true;
    }
  }
?>
