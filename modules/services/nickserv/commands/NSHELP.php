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
            if ($registration[2] == null || count($registration[2]) < 3) {
              continue;
            }
            $commands[strtoupper($registration[2][0])] = $registration[2];
          }
        }
        ksort($commands);
        $commands = array_values($commands);

        $title = "List of NickServ Commands";
        $message .= "|".str_repeat("=", floor((57 - strlen($title)) / 2))."[ ".
          $title." ]".str_repeat("=", floor((57 - strlen($title)) / 2))."|\r\n";
        foreach ($commands as $key => $command) {
          $helptext = explode("\r\n", $command[1]);
          $helptext[0] .= "\002".strtoupper($command[0])."\002 - ";
          foreach ($helptext as &$hline) {
            $hline = chunk_split($hline, 61, null);
          }
          foreach ($helptext as $l) {
            if (substr($l, -1) != " ") {
              $l .= "-";
            }
            $l .= "\r\n";
            if (strlen(trim($l)) > 0) {
              $message .= $l;
            }
          }
          if ($key !== (count($commands) - 1)) {
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
      EventHandling::registerForEvent("nsCommandEvent", $this,
        "receiveNickServCommand", array("help", "Shows a list of commands ".
        "when no parameter is provided and shows more detail about a command ".
        "when a parameter is provided.\r\nUsage: /msg NickServ HELP ".
        "[command]", null));
      return true;
    }
  }
?>
