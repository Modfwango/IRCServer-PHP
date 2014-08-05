<?php
  class @@CLASSNAME@@ {
    public $name = "PseudoConnection";

    public function isInstantiated() {
      return true;
    }
  }

  class PseudoConnection extends Connection {
    private $cbuffer = array();
    private $sbuffer = array();

    public function __construct() {
      $this->configured = true;
      $this->ip = "127.0.0.1";
      $this->host = "localhost";
      $this->localip = "127.0.0.1";
      $this->localhost = "localhost";
      $this->port = "0";
      $this->ssl = true;
      $this->type = "2";
      $this->created();
    }

    public function clientGetData() {
      if (count($this->sbuffer) > 0) {
        $data = array_shift($this->sbuffer);
        // Return the data.
        Logger::debug("Data received on '".$this->getConnectionString().
          "':  '".$data."'");
        return $data;
      }
      return false;
    }

    public function clientSend($data, $newline = true) {
      if (trim($data) != null) {
        Logger::debug("Sending data to server:  '".$data."'");
      }
      if ($newline == true) {
        $data .= "\r\n";
      }
      $this->cbuffer[] = $data;
      return true;
    }

    public function getData() {
      if (count($this->cbuffer) > 0) {
        $data = array_shift($this->cbuffer);
        // Return the data.
        Logger::debug("Data received on '".$this->getConnectionString().
          "':  '".$data."'");
        return $data;
      }
      return false;
    }

    public function isAlive() {
      return true;
    }

    public function send($data, $newline = true) {
      if (trim($data) != null) {
        Logger::debug("Sending data to client:  '".$data."'");
      }
      if ($newline == true) {
        $data .= "\r\n";
      }
      $this->sbuffer[] = $data;
      return true;
    }
  }
?>
