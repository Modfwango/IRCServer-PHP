<?php
  class __CLASSNAME__ {
    public $name = "ChannelJoinEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("channelJoinEvent", $this);
      return true;
    }
  }
?>
