<?php
  class @@CLASSNAME@@ {
    public $depend = array("Self", "WHOISResponseEvent");
    public $name = "OperatorInfoWHOISResponse";
    private $self = null;

    public function receiveWHOISResponse($name, $id, $data) {
      $source = $data[0];
      $target = $data[1];
      $response = $data[2];

      if ($target->getOption("operator") == true) {
        $weight = "58.5";
        if (!isset($response[$weight])) {
          $response[$weight] = array();
        }
        $response[$weight][] = ":".$this->self->getConfigFlag(
          "serverdomain")." 313 ".$source->getOption("nick")." ".
          $target->getOption("nick")." :is an IRC Operator";
        $data[2] = $response;
        return array(null, $data);
      }
      return array(true);
    }

    public function isInstantiated() {
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerAsEventPreprocessor("WHOISResponseEvent", $this,
        "receiveWHOISResponse");
      return true;
    }
  }
?>
