<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "Client", "CommandEvent", "Modes");
    public $name = "WHO";
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

      if ($connection->getOption("registered") == true) {
        if (count($command) > 0) {
          $modenames = array();
          $prefixes = array();
          foreach ($this->modes->getModesAndWeight() as $weight => $modes) {
            foreach ($modes as $mode) {
              $modenames[] = $mode[0];
              $prefixes[$mode[0]] = array($mode[4], $mode[5]);
            }
          }
          $match = "*";
          $users = array();
          $channel = $this->channel->getChannelByName($command[0]);
          if ($channel != false) {
            $match = $command[0];
            foreach ($channel["members"] as $member) {
              $c = $this->client->getClientByID($member);
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
              if (is_array($p)) {
                $p = array_shift($p);
              }
              $users[] = array($c, $p);
            }
          }
          else {
            if ($command[0] == "0" || $command[0] == "*") {
              $users = ConnectionManagement::getConnections();
            }
            else {
              foreach ($this->client->getClientsByMatchingHost($command[0])
                        as $client) {
                $users[] = $client;
              }
              foreach ($this->client->getClientsByMatchingIdent($command[0])
                        as $client) {
                $users[] = $client;
              }
              foreach ($this->client->getClientsByMatchingNick($command[0])
                        as $client) {
                $users[] = $client;
              }
              foreach ($this->client->getClientsByMatchingRealname(
                        $command[0]) as $client) {
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
            $connection->getOption("nick")." ".$command[0].
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
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "who");
      return true;
    }
  }
?>
