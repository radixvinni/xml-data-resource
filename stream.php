<?php
      require_once("render.php");

  abstract class BaseStream {
    protected	$position = 0,
        $length = 0,
        $data = "",
        $expire = 0,
        $path, $params = Array();

    protected	$scheme;

    function __construct() {
      $this->dom = new XMLGenerator;
      $this->dom->setRootName('data');
    }
    
    public function stream_flush() {
      return true;
    }
    
    
    public function stream_tell() {
      return $this->position;
    }
    
    
    public function stream_eof() {
      return $this->position >= $this->length;
    }
    
    
    public function stream_seek($offset, $whence) {
      switch($whence) {
        case SEEK_SET: {
          if($this->isValidOffset($offset)) {
            $this->position = $offset;
            return true;
          } else {
            return false;
          }
        }
        
        
        case SEEK_CUR: {
          if($offset >= 0) {
            $this->position += $offset;
            return true;
          } else {
            return false;
          }
        }
        
        
        case SEEK_END: {
          if($this->isValidOffset($this->position + $offset)) {
            $this->position = $this->length + $offset;
            return true;
          } else {
            return false;
          }
        }
        

        default: {
          return false;
        }
      }
    }
    
    
    public function url_stat() {
      return Array();
    }
    
    
    public function stream_stat() {
      return Array();
    }
    
    
    public function stream_close() {
      return true;
    }
    
    
    public function stream_read($count) {
      $result = substr($this->data, $this->position, $count);
      $this->position += $count;
      return $result;
    }
    
    
    public function stream_write($inputData) {
      $inputDataLength = strlen($inputData);

            $dataLeft = substr($this->data, 0, $this->position);
      $dataRight = substr($this->data, $this->position + $inputDataLength);

      $this->data = $dataLeft . $inputData . $dataRight;

      $this->position += $inputData;
      return $inputDataLength;
    }
    
    
    public function getProtocol() {
      return $this->scheme . "://";
    }
    
    
    protected function isValidOffset($offset) {
      return ($offset >= 0) && ($offset < $this->length);
    }
    
    
    protected function translateToXml($res = False, $def_key='key') {
            $this->dom->setDefaultKey($def_key);
      $data = $this->dom->getXML($res);
      $this->data = $data;
      $this->length = strlen($data);
    }
    
    
    protected function parsePath($path) {
      $protocol = $this->getProtocol();
      $path = substr($path, strlen($protocol));
      
      $parsed_url = parse_url($path);
      $this->path = $parsed_url['path'];
      
      if($params = @$parsed_url['query']) {
        parse_str($params, $params_arr);
        $this->params = $params_arr;
        
        if(isset($params_arr['expire'])) {
          $this->expire = $params_arr['expire'];
        }
        
      }
      return $this->path;
    }
    
    
    static public function registerStream($scheme) {
      if(!stream_wrapper_register($scheme, "{$scheme}Stream")) {
          throw new coreException("Failed to register stream \"{$scheme}\"");
        }
      
    }
  };
?>
