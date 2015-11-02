<?php 
require_once('stream.php');

    class funcStream extends BaseStream {
        protected $scheme = "func";

            function find_all_files($dir) 
            { 
                $result = array();
                $root = scandir($dir); 
                foreach($root as $value) 
                { 
                    if(!$value) continue;
                    if(strpos($value, ".") === 0) continue; 
                    if(is_file("$dir/$value")) {
                        if(filesize("$dir/$value"))
                            $result[]=explode('/',"$dir/$value");
                        continue;
                        }
                    foreach($this->find_all_files("$dir/$value") as $value) 
                        $result[]=$value; 
                } 
                return $result; 
            } 
            function load_file($f) 
            {
              $ret = array();
              foreach(file($f, FILE_IGNORE_NEW_LINES) as $s)
                $ret[] = explode(':',$s);
              return $ret;
            } 

        public function stream_open($path, $mode, $options, $opened_path) {
            $path = $this->parsePath($path);

            if($path == 'escape') {
                if (strpos($this->params['f'], '/') !== false)
                            die('Not allowed!');

                        $content = file_get_contents('xsl/'.$this->params['f']); 
                        $data = "<file>".htmlspecialchars($content)."</file>";
                  $this->data = $data;
                  $this->length = strlen($data);
                
            } elseif($path == 'mac_levels') {
                  $this->translateToXml($this->load_file('/etc/security/mac_levels'));
            } elseif($path == 'mac_categories') {
                  $this->translateToXml($this->load_file('/etc/security/mac_categories'));
            } elseif($path == 'list_xsl') {
                  $this->translateToXml(scandir('xsl'));
            } elseif($path == 'list_data') {
                  if (strpos($this->params['d'], '/') !== false)
                            die('Not allowed!');
                  $this->translateToXml($this->find_all_files('data/'.$this->params['d']));
            } else {
                return false;
            }

            return true;
        }
    };
?>
