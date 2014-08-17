<?php
  class __CLASSNAME__ {
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
