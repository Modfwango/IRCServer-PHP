<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "Client", "CommandEvent", "Modes");
    public $name = "NAMES";
    private $channel = null;
    private $client = null;
    private $modes = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if (strtolower($command[0]) == "names") {
        if ($connection->getOption("registered") == true) {
          if (count($command) > 1) {
            $channels = array($command[1]);
            if (stristr($command[1], ",")) {
              $channels = explode(",", $command[1]);
            }
            $modenames = array();
            $prefixes = array();
            foreach ($this->modes->getPrefixes() as $prefix) {
              $name = $this->modes->getModeNameByChar("0", $prefix[1]);
              if ($name != false) {
                $modenames[] = $name;
                $prefixes[$name] = array($prefix[0], $prefix[2]);
              }
            }
            foreach ($channels as $channel) {
              $channel = $this->channel->getChannelByName($channel);
              if ($channel != false) {
                $members = array();
                foreach ($channel["members"] as $id) {
                  $c = $this->client->getClientByID($id);
                  if ($c != false) {
                    $p = array();
                    $has = $this->channel->hasModes($channel["name"],
                      $modenames);
                    if ($has != false) {
                      foreach ($has as $m) {
                        if ($m["param"] == $c->getOption("nick")
                            && isset($prefixes[$m["name"]])) {
                          if (!isset($p[$prefixes[$m["name"]][1]])) {
                            $p[$prefixes[$m["name"]][1]] = array();
                          }
                          $p[$prefixes[$m["name"]][1]][] =
                            $prefixes[$m["name"]][0];
                        }
                      }
                    }
                    ksort($p);
                    Logger::info(var_export($p, true));
                    $p = array_pop($p);
                    $members[] = $p[0].$c->getOption("nick");
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
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
