<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "Modes", "Numeric", "Self",
      "ShouldExposeChannelToUserEvent", "Util", "WHOISResponseEvent");
    public $name = "ChannelInfoWHOISResponse";
    private $channel = null;
    private $modes = null;
    private $numeric = null;
    private $self = null;
    private $util = null;

    public function receiveWHOISResponse($name, $id, $data) {
      $source = $data[0];
      $target = $data[1];
      $response = $data[2];

      $weight = "83.5";
      if (!isset($response[$weight])) {
        $response[$weight] = array();
      }
      $membership = $this->channel->getChannelMembershipByID(
        $target->getOption("id"));
      $ret = array();
      if (count($membership) > 0) {
        foreach ($membership as $channel) {
          $c = $this->channel->getChannelByName($channel);
          if ($c != false) {
            $event = EventHandling::getEventByName(
              "shouldExposeChannelToUserEvent");
            if ($event != false) {
              foreach ($event[2] as $id => $registration) {
                // Trigger the shouldExposeChannelToUserEvent event for each
                // registered module.
                if (!EventHandling::triggerEvent(
                    "shouldExposeChannelToUserEvent", $id,
                    array($source, $channel))) {
                  continue 2;
                }
              }
            }

            $ret[] = $this->channel->getChannelMemberPrefixByID($channel,
              $target->getOption("id"), false).$channel;
          }
        }
      }
      if (count($ret) > 0) {
        foreach ($this->util->getStringsWithBaseAndMaxLengthAndObjects(
            $this->numeric->get("RPL_WHOISCHANNELS", array(
              $this->self->getConfigFlag("serverdomain"),
              $source->getOption("nick"),
              $target->getOption("nick"),
              null
            )), $ret, false, 510) as $line) {
          $response[$weight][] = $line;
        }
        $data[2] = $response;
        return array(null, $data);
      }
      return array(true);
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      $this->util = ModuleManagement::getModuleByName("Util");
      EventHandling::registerAsEventPreprocessor("WHOISResponseEvent", $this,
        "receiveWHOISResponse");
      return true;
    }
  }
?>
