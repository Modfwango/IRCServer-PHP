<?php
  class @@CLASSNAME@@ {
    public $depend = array("WHOISResponseEvent");
    public $name = "IdleInfoWHOISResponse";

    public function receiveWHOISResponse($name, $id, $data) {
      $source = $data[0];
      $target = $data[1];
      $response = $data[2];
      Logger::info($this->name);

      $weight = "33";
      if (!isset($response[$weight])) {
        $response[$weight] = array();
      }
      $response[$weight][] = ":".__SERVERDOMAIN__." 317 ".
        $source->getOption("nick")." ".$target->getOption("nick")." 0 ".
        $target->getOption("signon")." :seconds idle, signon time";
      $data[2] = $response;
      return array(null, $data);
    }

    public function isInstantiated() {
      EventHandling::registerAsEventPreprocessor("WHOISResponseEvent", $this,
        "receiveWHOISResponse");
      return true;
    }
  }
?>
