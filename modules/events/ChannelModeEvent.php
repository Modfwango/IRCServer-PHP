<?php
  class __CLASSNAME__ {
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
