<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "ChannelMessageEvent", "ChannelModeEvent",
      "Modes");
    public $name = "StripColors";
    private $channel = null;
    private $modes = null;

    public function receiveChannelMode($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $has = $this->channel->hasModes($channel["name"],
        array("StripColors"));
      foreach ($modes as $key => $mode) {
        if ($mode["name"] == "StripColors") {
          if ($mode["operation"] == "+") {
            if ($has != false) {
              unset($modes[$key]);
            }
            else {
              $has = true;
            }
          }
          if ($mode["operation"] == "-") {
            if ($has == false) {
              unset($modes[$key]);
            }
            else {
              $has = false;
            }
          }
        }
      }
      $data[2] = $modes;
      return array(null, $data);
    }

    public function receiveChannelMessage($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $message = $data[2];

      $modes = $this->channel->hasModes($channel["name"],
        array("StripColors"));
      if ($modes != false) {
        $message = preg_replace("/[\x02\x1F\x0F\x16]|\x03(\d\d?(,\d\d?)?)?/",
          null, $message);
        $data[2] = $message;
        return array(null, $data);
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->modes->setMode(array("StripColors", "c", "0", "0"));
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      EventHandling::registerAsEventPreprocessor("channelMessageEvent", $this,
        "receiveChannelMessage");
      return true;
    }
  }
?>
