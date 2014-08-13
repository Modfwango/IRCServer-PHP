<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "Client", "ChannelModeEvent",
      "InviteOnly", "Modes");
    public $name = "InviteException";
    private $channel = null;
    private $client = null;
    private $modes = null;

    public function receiveChannelMode($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $h = array();
      $has = $this->channel->hasModes($channel["name"],
        array("InviteException"));
      if (is_array($has) && count($has) > 0) {
        foreach ($has as $m) {
          $h[strtolower($m["param"])] = true;
        }
      }
      foreach ($modes as $key => &$mode) {
        if ($mode["name"] == "InviteException") {
          $mode["param"] = $this->client->getPrettyMask($mode["param"]);
          if (!isset($h[strtolower($mode["param"])])) {
            $h[strtolower($mode["param"])] = false;
          }
          if ($mode["operation"] == "+") {
            if ($h[strtolower($mode["param"])] != false) {
              unset($modes[$key]);
            }
            else {
              $h[strtolower($mode["param"])] = true;
            }
          }
          if ($mode["operation"] == "-") {
            if ($h[strtolower($mode["param"])] == false) {
              unset($modes[$key]);
            }
            else {
              $h[strtolower($mode["param"])] = false;
            }
          }
        }
      }
      $data[2] = $modes;
      return array(null, $data);
    }

    public function receiveInviteOnlyShouldPreventAction($name, $data) {
      $source = $data[0];
      $channel = $data[1];

      $modes = $this->channel->hasModes($channel,
        array("InviteException"));
      if ($modes != false) {
        foreach ($modes as $mode) {
          if ($this->client->clientMatchesMask($source, $mode["param"])) {
            // Ban is exempted.
            return false;
          }
        }
      }

      // Ban is not exempted.
      return true;
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->modes->setMode(array("InviteException", "I", "0",
        "3"));
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      EventHandling::registerForEvent("inviteOnlyShouldPreventActionEvent",
        $this, "receiveInviteOnlyShouldPreventAction");
      return true;
    }
  }
?>
