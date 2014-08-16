<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "Modes", "WHOISResponseEvent");
    public $name = "ChannelInfoWHOISResponse";

    public function receiveWHOISResponse($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $response = $data[2];

      $membership = $this->channel->getChannelMembershipByID(
        $target->getOption("id")));
      $ret = array();
      if (count($membership) > 0) {
        $weight = "83.5";
        if (!isset($response[$weight])) {
          $response[$weight] = array();
        }
        $modenames = array();
        $prefixes = array();
        foreach ($this->modes->getModesAndWeight() as $weight => $modes) {
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
                  continue;
                }
              }
            }

            $prefix = array("", 0);
            $has = $this->channel->hasModes($channel, $modenames);
            if ($has != false) {
              foreach ($has as $m) {
                if ($m["param"] == $target->getOption("nick")) {
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
        foreach ($this->getStringsWithBaseAndMaxLengthAndObjects(":".
          __SERVERDOMAIN__." 319 ".$source->getOption("nick")." ".
          $target->getOption("nick")." :", $ret, false, 510) as $line) {
          $response[$weight][] = $line;
        }
        $data[2] = $response;
        return array(null, $data);
      }
      return array(true);
    }

    public function getStringsWithBaseAndMaxLengthAndObjects($base, $objects,
        $includeFirstSpace = true, $maxLength = 0, $maxObjects = 0) {
      $ret = array();
      if (count($objects) > 0) {
        foreach ($objects as $key => $object) {
          $objects[$key] = " ".$object;
        }
        while (count($objects) > 0) {
          $objCount = 0;
          $string = $base;
          if ($includeFirstSpace == false) {
            $objects[0] = substr($objects[0], 1);
          }
          while (true) {
            if (count($objects) == 0
                || ($maxLength > 0
                && strlen($string.$objects[0]) >= $maxLength)
                || ($maxObjects > 0 && $objCount > $maxObjects)) {
              break;
            }
            $string .= array_shift($objects);
            $objCount++;
          }
          $ret[] = $string;
        }
      }
      return $ret;
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      EventHandling::registerAsEventPreprocessor("WHOISResponseEvent", $this,
        "receiveWHOISResponse");
      return true;
    }
  }
?>
