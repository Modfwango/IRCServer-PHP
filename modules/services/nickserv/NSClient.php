<?php
  class @@CLASSNAME@@ {
    public $depend = array("Client", "NSCommandEvent", "PseudoConnection",
      "Quit", "UserRegistrationEvent");
    public $name = "NSClient";
    private $pclient = null;

    public function receivePrivateMessage($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $message = $data[2];
      $ex = explode(" ", $message);
      $cmd = $ex[0];
      unset($ex[0]);
      $ex = array_values($ex);

      if (strtolower($target->getOption("nick")) == "nickserv") {
        $count = 0;
        $event = EventHandling::getEventByName("nsCommandEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            if ($registration[2] == null || count($registration[2]) < 3 ||
                strtolower(trim($registration[2][0]))
                != strtolower(trim($cmd))) {
              continue;
            }
            // Trigger the nsCommandEvent event for each
            // registered module.
            $count++;
            EventHandling::triggerEvent("nsCommandEvent", $id,
              array($source, $target, $ex));
          }
        }
        if ($count == 0) {
          // Command doesn't exist.
          $source->send(":".$target->getOption("nick")."!".
            $target->getOption("ident")."@".$target->getHost()." PRIVMSG ".
            $source->getOption("nick")." :That command doesn't exist.  For ".
              "help, type /msg NickServ help");
        }
      }
    }

    public function isInstantiated() {
      // Kill any pre-existing connection with "NickServ" as its nickname.
      $client = ModuleManagement::getModuleByName("Client");
      $cc = $client->getClientByNick("NickServ");
      if ($cc != false) {
        $quit = ModuleManagement::getModuleByName("Quit");
        $quit->notifyQuit(null, $cc,
          "Killed:  Ownership of nickname taken by services.");
        $cc->disconnect();
      }

      // Create a pseudo-connection to serve as the NickServ client.
      $this->pclient = new PseudoConnection();
      ConnectionManagement::newConnection($this->pclient);
      $this->pclient->setOption("nick", "NickServ");
      $this->pclient->setOption("ident", "services");
      $this->pclient->setOption("realname", "Nickname Services Client");

      // TODO: Move registration notification into a method.
      // Mark the pseudo-connection as registered and trigger event.
      $this->pclient->setOption("registered", true);
      $event = EventHandling::getEventByName("userRegistrationEvent");
      if ($event != false) {
        foreach ($event[2] as $id => $registration) {
          // Trigger the userRegistrationEvent event for each registered
          // module.
          EventHandling::triggerEvent("userRegistrationEvent", $id,
              $this->pclient);
        }
      }

      EventHandling::registerForEvent("privateMessageEvent", $this,
        "receivePrivateMessage");
      return true;
    }
  }
?>
