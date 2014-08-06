<?php
  class @@CLASSNAME@@ {
    public $depend = array("NSClient", "Util");
    public $name = "NSHELP";

    public function receiveNickServCommand($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $params = $data[2];

      if (count($params) > 0) {
        $command = null;
        $event = EventHandling::getEventByName("nsCommandEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            if ($registration[2] == null || count($registration[2]) < 3 ||
                strtolower(trim($registration[2][0]))
                != strtolower(trim($params[0]))) {
              continue;
            }
            $command = $registration[2];
          }
        }

        if (is_array($command)) {
          $title = "\002".strtoupper($command[0])."\002 Command Help";
          $message = "|".str_repeat("=", ceil((56 - strlen($title)) / 2))."[ ".
            $title." ]".str_repeat("=", floor((56 - strlen($title)) / 2)).
            "|\r\n";
          $message .= $this->util->prettyStrChunk($command[1]."\r\n".
            $command[2], 64, "\r\n");
          $lines = explode("\r\n", trim($message));
          foreach ($lines as $line) {
            $source->send(":".$target->getOption("nick")."!".
              $target->getOption("ident")."@".$target->getHost()." PRIVMSG ".
              $source->getOption("nick")." :".$line);
          }
        }
        else {
          $source->send(":".$target->getOption("nick")."!".
            $target->getOption("ident")."@".$target->getHost()." PRIVMSG ".
            $source->getOption("nick")." :That command doesn't exist.  For ".
              "help, type /msg NickServ help");
        }
      }
      else {
        $commands = array();
        $event = EventHandling::getEventByName("nsCommandEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            if ($registration[2] == null || count($registration[2]) < 3) {
              continue;
            }
            $commands[strtoupper($registration[2][0])] = $registration[2];
          }
        }
        ksort($commands);
        $commands = array_values($commands);

        $title = "List of NickServ Commands";
        $message = "|".str_repeat("=", ceil((56 - strlen($title)) / 2))."[ ".
          $title." ]".str_repeat("=", floor((56 - strlen($title)) / 2))."|\r\n";
        foreach ($commands as $key => $command) {
          $message .= $this->util->prettyStrChunk("\002".strtoupper(
            $command[0])."\002 - ".$command[1], 64, "\r\n");
          if ($key != (count($commands) - 1)) {
            $message .= str_repeat("=", 62)."\r\n";
          }
        }
        $lines = explode("\r\n", trim($message));
        foreach ($lines as $line) {
          $source->send(":".$target->getOption("nick")."!".
            $target->getOption("ident")."@".$target->getHost()." PRIVMSG ".
            $source->getOption("nick")." :".$line);
        }
      }
    }

    public function isInstantiated() {
      $this->util = ModuleManagement::getModuleByName("Util");
      EventHandling::registerForEvent("nsCommandEvent", $this,
        "receiveNickServCommand", array("help", "Shows a list of commands ".
        "when no parameter is provided and shows more detail about a command ".
        "when a parameter is provided.\nUsage: /msg NickServ HELP ".
        "[command]", "This is a test of extra examples even though the help ".
        "command doesn't have any examples"));
      return true;
    }
  }
?>
