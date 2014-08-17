<?php
  class __CLASSNAME__ {
    public $name = "ChannelNoticeEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("channelNoticeEvent", $this);
      return true;
    }
  }
?>
