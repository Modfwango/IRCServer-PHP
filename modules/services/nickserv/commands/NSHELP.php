<?php
  class @@CLASSNAME@@ {
    public $depend = array("NSClient");
    public $name = "NSHELP";

    public function receiveNickServCommand($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $params = $data[2];

      if (count($params) > 0) {

      }
      else {
        $commands = array();
        $event = EventHandling::getEventByName("nsCommandEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            $commands[] = $registration[2];
          }
        }
        sort($commands);
        $lines = array();
        while (count($commands) > 0) {
          if (strlen($commands[0]) > 95) {
            array_shift($commands);
          }
          $curLine = null;
          while (strlen($curLine." ".$commands[0]) < 96) {
            $curLine .= " ".strtoupper(array_shift($commands));
          }
          $lines[] = $curLine;
        }
        $title = "Here is a list of NickServ commands:";
        $message = str_repeat("=", 96)."\n";
        $message .= "|".str_repeat(" ", floor((94 - strlen($title)) / 2)).
          $title."|\n";
        $message .= str_repeat("=", 96)."\n";
        $message .= implode("\n", $lines);
        $lines = explode("\n", $message);
        foreach ($lines as $line) {
          $source->send(":".$target->getOption("nick")."!".
            $target->getOption("ident")."@".$target->getHost()." PRIVMSG ".
            $source->getOption("nick")." :".$line);
        }
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("nsCommandEvent", $this,
        "receiveNickServCommand", "help");
      return true;
    }
  }
?>