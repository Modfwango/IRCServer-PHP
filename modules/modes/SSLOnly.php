<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "Client", "ChannelJoinEvent",
      "ChannelModeEvent", "Modes", "Numeric", "Self");
    public $name = "SSLOnly";
    private $channel = null;
    private $client = null;
    private $modes = null;
    private $numeric = null;
    private $self = null;

    public function receiveChannelMode($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $has = $this->channel->hasModes($channel["name"], array("SSLOnly"));

      $channelHasPTClients = false;
      if ($has == false) {
        if ($source->getSSL() == false) {
          $channelHasPTClients = true;
        }
        else {
          $members = $this->channel->getChannelMembers($channel["name"]);
          if (is_array($members)) {
            foreach ($members as $member) {
              $client = $this->client->getClientByID($member);
              if ($client->getSSL() == false) {
                $channelHasPTClients = true;
                break;
              }
            }
          }
        }
      }

      $sendWarning = false;
      foreach ($modes as $key => $mode) {
        if ($mode["name"] == "SSLOnly") {
          if ($mode["operation"] == "+") {
            if ($has != false) {
              unset($modes[$key]);
            }
            else {
              if ($channelHasPTClients == true) {
                unset($modes[$key]);
                $sendWarning = true;
              }
              else {
                $has = true;
              }
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
      if ($sendWarning == true) {
        $source->send($this->numeric->get("ERR_SECUREONLYCHAN", array(
          $this->self->getConfigFlag("serverdomain"),
          $source->getOption("nick"),
          $channel["name"]
        )));
      }
      $data[2] = $modes;
      return array(null, $data);
    }

    public function receiveChannelJoin($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];

      $modes = $this->channel->hasModes($channel, array("SSLOnly"));
      if ($modes != false && $source->getSSL() == false) {
        $source->send($this->numeric->get("ERR_SECUREONLYCHAN", array(
          $this->self->getConfigFlag("serverdomain"),
          $source->getOption("nick"),
          $channel
        )));
        return array(false);
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      $this->modes->setMode(array("SSLOnly", "S", "0", "0"));
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      EventHandling::registerAsEventPreprocessor("channelJoinEvent", $this,
        "receiveChannelJoin");
      return true;
    }
  }
?>
