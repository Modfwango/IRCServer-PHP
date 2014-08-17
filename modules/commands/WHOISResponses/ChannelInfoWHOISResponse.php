<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "Modes", "Self", "Util",
      "WHOISResponseEvent");
    public $name = "ChannelInfoWHOISResponse";
    private $channel = null;
    private $modes = null;
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
        $modenames = array();
        $prefixes = array();
        foreach ($this->modes->getModesAndWeight() as $modes) {
          foreach ($modes as $mode) {
            $modenames[] = $mode[0];
            $prefixes[$mode[0]] = array($mode[4], $mode[5]);
          }
        }
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

            $prefix = array("", 0);
            $has = $this->channel->hasModes($channel, $modenames);
            if ($has != false) {
              foreach ($has as $m) {
                if ($m["param"] == $target->getOption("id")) {
                  if ($prefixes[$m["name"]][1] > $prefix[1]) {
                    $prefix = $prefixes[$m["name"]];
                  }
                }
              }
            }
            $ret[] = $prefix[0].$channel;
          }
        }
      }
      if (count($ret) > 0) {
        foreach ($this->util->getStringsWithBaseAndMaxLengthAndObjects(":".
            $this->self->getConfigFlag("serverdomain")." 319 ".
            $source->getOption("nick")." ".$target->getOption("nick")." :",
            $ret, false, 510) as $line) {
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
      $this->self = ModuleManagement::getModuleByName("Self");
      $this->util = ModuleManagement::getModuleByName("Util");
      EventHandling::registerAsEventPreprocessor("WHOISResponseEvent", $this,
        "receiveWHOISResponse");
      return true;
    }
  }
?>
