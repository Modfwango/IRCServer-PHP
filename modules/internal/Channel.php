<?php
  class @@CLASSNAME@@ {
    public $depend = array("ChannelJoinEvent", "ChannelMessageEvent",
      "NickChangeEvent", "UserQuitEvent");
    public $name = "Channel";
    private $options = array();

    public function getOption($key) {
      // Retrieve the requested option if it exists, otherwise return false.
      return (isset($this->options[$key]) ? $this->options[$key] : false);
    }

    public function receiveChannelJoin($name, $data) {

    }

    public function receiveChannelMessage($name, $data) {

    }

    public function receiveNickChange($name, $data) {

    }

    public function setOption($key, $value) {
      // Set an option for this connection.
      $this->options[$key] = $value;
      return true;
    }

    public function receiveUserQuit($name, $data) {

    }

    public function isInstantiated() {
      EventHandling::registerForEvent("channelJoinEvent", $this,
        "receiveChannelJoin");
      EventHandling::registerForEvent("channelMessageEvent", $this,
        "receiveChannelMessage");
      EventHandling::registerForEvent("nickChangeEvent", $this,
        "receiveNickChange");
      EventHandling::registerForEvent("userQuitEvent", $this,
        "receiveUserQuit");
      return true;
    }
  }
?>
