<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "ChannelMessageEvent", "ChannelModeEvent",
      "ChannelNoticeEvent", "Modes", "Numeric", "Self");
    public $name = "Moderated";
    private $channel = null;
    private $modes = null;
    private $numeric = null;
    private $self = null;

    public function receiveChannelMode($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $has = $this->channel->hasModes($channel["name"],
        array("Moderated"));
      foreach ($modes as $key => $mode) {
        if ($mode["name"] == "Moderated") {
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

    public function receiveChannelEvent($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $message = $data[2];

      $modes = $this->channel->hasModes($channel["name"],
        array("Moderated"));
      if ($modes != false) {
        $modes = $this->channel->hasModes($channel["name"],
          array("ChannelVoice", "ChannelOperator"));
        if ($modes != false) {
          foreach ($modes as $mode) {
            if ($mode["param"] == $source->getOption("id")) {
              return array(true);
            }
          }
        }
        $source->send($this->numeric->get("ERR_CANNOTSENDTOCHAN", array(
          $this->self->getConfigFlag("serverdomain"),
          $source->getOption("nick"),
          $channel["name"]
        )));
        return array(false);
      }
    }

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      $this->modes->setMode(array("Moderated", "m", "0", "0"));
      EventHandling::registerAsEventPreprocessor("channelMessageEvent", $this,
        "receiveChannelEvent");
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      EventHandling::registerAsEventPreprocessor("channelNoticeEvent", $this,
        "receiveChannelEvent");
      return true;
    }
  }
?>
