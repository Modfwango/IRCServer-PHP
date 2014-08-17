<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "Client", "ChannelMessageEvent",
      "ChannelNoticeEvent", "UserModeEvent", "Modes");
    public $name = "Deaf";
    private $channel = null;
    private $client = null;
    private $modes = null;

    public function receiveUserMode($name, $id, $data) {
      $source = $data[0];
      $modes = $data[1];

      $has = $this->client->hasModes($source->getOption("id"),
        array("Deaf"));
      foreach ($modes as $key => $mode) {
        if ($mode["name"] == "Deaf") {
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

      if (isset($data[3])) {
        $exceptions = $data[3];
      }
      else {
        $exceptions = array();
      }

      $members = $this->channel->getChannelMembers($channel["name"]);
      if (is_array($members)) {
        foreach ($members as $member) {
          $modes = $this->client->hasModes($member, array("Deaf"));
          if ($modes != false) {
            $exceptions[] = $member;
          }
        }
        $data[3] = $exceptions;
        return array(null, $data);
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->modes->setMode(array("Deaf", "d", "1", "0"));
      EventHandling::registerAsEventPreprocessor("userModeEvent", $this,
        "receiveUserMode");
      EventHandling::registerAsEventPreprocessor("channelMessageEvent", $this,
        "receiveChannelEvent");
      EventHandling::registerAsEventPreprocessor("channelNoticeEvent", $this,
        "receiveChannelEvent");
      return true;
    }
  }
?>
