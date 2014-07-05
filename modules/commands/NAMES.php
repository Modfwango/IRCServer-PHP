<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "CommandEvent");
    public $name = "NAMES";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if (strtolower($command[0]) == "names") {
        if ($connection->getOption("registered") == true) {
          if (count($command) > 1) {
            $channels = array($command[1]);
            if (stristr($command[1], ",")) {
              $channels = explode(",", $command[1]);
            }
            foreach ($channels as $channel) {
              $channel = ModuleManagement::getModuleByName("Channel")->
                getChannelByName($channel);
              if ($channel != false) {
                $members = array();
                foreach ($channel["members"] as $id) {
                  foreach (ConnectionManagement::getConnections() as $c) {
                    if ($c->getOption("id") == $id) {
                      $members[] = $c->getOption("nick");
                    }
                  }
                }

                $base = ":".__SERVERDOMAIN__." 353 ".
                  $connection->getOption("nick")." = ".$channel["name"]." :";
                $remaining = (510 - strlen($base));
                foreach ($members as $member) {
                  $remaining -= (strlen($member) + 1);
                  if ($remaining > -2) {
                    if (!isset($items)) {
                      $items = array();
                    }
                    $items[] = $member;
                  }
                  else {
                    $remaining = (510 - strlen($base));
                    $connection->send($base.implode(" ", $items));
                    unset($items);
                  }
                }
                if (isset($items)) {
                  $connection->send($base.implode(" ", $items));
                }
              }
            }
            if (count($channels) == 1) {
              $connection->send(":".__SERVERDOMAIN__." 366 ".
                $connection->getOption("nick")." ".$channels[0].
                " :End of /NAMES list.");
              return true;
            }
          }
          $connection->send(":".__SERVERDOMAIN__." 366 ".
            $connection->getOption("nick")." * :End of /NAMES list.");
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 451 ".(
            $connection->getOption("nick") ? $connection->getOption("nick") :
            "*")." :You have not registered");
        }
        return true;
      }
      return false;
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
