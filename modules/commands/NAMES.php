<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "Client", "CommandEvent", "Modes",
      "Self", "Util");
    public $name = "NAMES";
    private $channel = null;
    private $client = null;
    private $modes = null;
    private $self = null;
    private $util = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if ($connection->getOption("registered") == true) {
        if (count($command) > 0) {
          $channels = array($command[0]);
          if (stristr($command[0], ",")) {
            $channels = explode(",", $command[0]);
          }
          $modenames = array();
          $prefixes = array();
          foreach ($this->modes->getModesAndWeight() as $weight => $modes) {
            foreach ($modes as $mode) {
              $modenames[] = $mode[0];
              $prefixes[$mode[0]] = array($mode[4], $mode[5]);
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
                  $p = array_pop($p);
                  if (is_array($p)) {
                    $p = array_shift($p);
                  }
                  $members[] = $p.$c->getOption("nick");
                }
              }

              $base = ":".$this->self->getConfigFlag("serverdomain")." 353 ".
                $connection->getOption("nick")." = ".$channel["name"]." :";
              foreach ($this->util->getStringsWithBaseAndMaxLengthAndObjects(
                        $base, $members, false, 510) as $line) {
                $connection->send($line);
              }
            }
          }
          if (count($channels) == 1) {
            $connection->send(":".$this->self->getConfigFlag(
              "serverdomain")." 366 ".$connection->getOption("nick")." ".
              $channels[0]." :End of /NAMES list.");
            return true;
          }
        }
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 366 ".$connection->getOption("nick")." * :End of ".
          "/NAMES list.");
      }
      else {
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 451 ".($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")." :You have not registered");
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->self = ModuleManagement::getModuleByName("Self");
      $this->util = ModuleManagement::getModuleByName("Util");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "names");
      return true;
    }
  }
?>
