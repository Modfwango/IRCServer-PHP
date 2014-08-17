<?php
  class __CLASSNAME__ {
    public $name = "ChannelInviteEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("channelInviteEvent", $this);
      return true;
    }
  }
?>
