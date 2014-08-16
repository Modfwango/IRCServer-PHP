<?php
  class @@CLASSNAME@@ {
    public $depend = array("WHOISResponseEvent");
    public $name = "OperatorInfoWHOISResponse";

    public function receiveWHOISResponse($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $response = $data[2];
      Logger::info(var_export($target, true));
      if ($target->getOption("operator") == true) {
        $weight = "50.5";
        if (!isset($response[$weight])) {
          $response[$weight] = array();
        }
        $response[$weight][] = ":".__SERVERDOMAIN__." 313 ".
          $source->getOption("nick")." ".$target->getOption("nick")." :is an ".
          "IRC Operator";
        $data[2] = $response;
        return array(null, $data);
      }
      return array(true);
    }

    public function isInstantiated() {
      EventHandling::registerAsEventPreprocessor("WHOISResponseEvent", $this,
        "receiveWHOISResponse");
      return true;
    }
  }
?>
