<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "ChannelCreatedEvent", "ChannelModeEvent",
      "ChannelTopicEvent", "Modes", "Numeric", "Self");
    public $name = "ProtectTopic";
    private $channel = null;
    private $modes = null;
    private $numeric = null;
    private $self = null;

    public function receiveChannelCreated($name, $id, $channel) {
      if (!isset($channel["modes"])) {
        $channel["modes"] = array();
      }
      $channel["modes"][] = array(
        "name" => "ProtectTopic"
      );
      $this->channel->setChannel($channel);
      return array(null, $channel);
    }

    public function receiveChannelMode($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $has = $this->channel->hasModes($channel["name"], array("ProtectTopic"));
      foreach ($modes as $key => $mode) {
        if ($mode["name"] == "ProtectTopic") {
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

    public function receiveChannelTopic($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $message = $data[2];

      $modes = $this->channel->hasModes($channel["name"],
        array("ProtectTopic"));
      if ($modes != false) {
        $modes = $this->channel->hasModes($channel["name"],
          array("ChannelOperator"));
        if ($modes != false) {
          foreach ($modes as $mode) {
            if ($mode["param"] == $source->getOption("id")) {
              return array(true);
            }
          }
        }
        $source->send($this->numeric->get("ERR_CHANOPRIVSNEEDED", array(
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
      $this->modes->setMode(array("ProtectTopic", "t", "0", "0"));
      EventHandling::registerAsEventPreprocessor("channelCreatedEvent", $this,
        "receiveChannelCreated");
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      EventHandling::registerAsEventPreprocessor("channelTopicEvent", $this,
        "receiveChannelTopic");
      return true;
    }
  }
?>
