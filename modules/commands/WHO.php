<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "Client", "CommandEvent", "Modes");
    public $name = "WHO";
    private $channel = null;
    private $client = null;
    private $modes = null;

    private function getHighestPrefix($prefixes, $modenames, $channel, $c) {
      $p = array();
      $has = $this->channel->hasModes($channel["name"], $modenames);
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
      end($p);
      $weight = key($p);
      $p = array_pop($p);
      return array($weight => $p[0]);
    }

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if (strtolower($command[0]) == "who") {
        if ($connection->getOption("registered") == true) {
          if (count($command) > 1) {
            $modenames = array();
            $prefixes = array();
            foreach ($this->modes->getPrefixes() as $prefix) {
              $name = $this->modes->getModeNameByChar("0", $prefix[1]);
              if ($name != false) {
                $modenames[] = $name;
                $prefixes[$name] = array($prefix[0], $prefix[2]);
              }
            }
            $match = "*";
            $users = array();
            $channel = $this->channel->getChannelByName($command[1]);
            if ($channel != false) {
              $match = $command[1];
              foreach ($channel["members"] as $member) {
                $c = $this->client->getClientByID($member);
                $prefix = $this->getHighestPrefix($prefixes, $modenames,
                  $channel, $c);
                $users[] = array($c, array_shift($prefix));
              }
            }
            else {
              if ($command[1] == "0" || $command[1] == "*") {
                $users = ConnectionManagement::getConnections();
              }
              else {
                foreach ($this->client->getClientsByMatchingHost($command[1])
                          as $client) {
                  $users[] = $client;
                }
                foreach ($this->client->getClientsByMatchingIdent($command[1])
                          as $client) {
                  $users[] = $client;
                }
                foreach ($this->client->getClientsByMatchingNick($command[1])
                          as $client) {
                  $users[] = $client;
                }
                foreach ($this->client->getClientsByMatchingRealname(
                          $command[1]) as $client) {
                  $users[] = $client;
                }
              }
            }
            foreach ($users as $user) {
              $prefix = null;
              if (is_array($user)) {
                $prefix = $user[1];
                $user = $user[0];
              }
              $connection->send(":".__SERVERDOMAIN__." 352 ".
                $connection->getOption("nick")." ".$match." ".
                $user->getOption("ident")." ".$user->getHost()." ".
                __SERVERDOMAIN__." ".$user->getOption("nick").
                " H".$prefix." :0 ".$user->getOption("realname"));
            }
            $connection->send(":".__SERVERDOMAIN__." 315 ".
              $connection->getOption("nick")." ".$command[1].
              " :End of /WHO list.");
          }
          else {
            $connection->send(":".__SERVERDOMAIN__." 461 ".
              $connection->getOption("nick")." WHO :Not enough parameters");
          }
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
