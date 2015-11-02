<?php
require_once('config.php');

class XMLGenerator {
     
    private $writer;
    private $version = '1.0';
    private $encoding = 'UTF-8';
    private $rootName = 'data';
    private $def_key = 'key';
    private $attributes = array();
 
    function __construct() {
        $this->writer = new XMLWriter();
        $this->writer->openMemory();
        $this->writer->startDocument($this->version, $this->encoding);
    }
     
    public function getXML($data=False) {
        if (is_array($data)) {
            $this->convert($data);
        }
        $this->writer->endElement();
        return $this->writer->outputMemory();
    }
    public function setVersion($version) {
        $this->version = $version;
    }
    public function setAttribute($k, $v) {
        $this->writer->writeAttribute($k, $v);
    }
    public function setEncoding($encoding) {
        $this->encoding = $encoding;
    }
    public function setRootName($rootName) {
        $this->rootName = $rootName;
        $this->writer->startElement($this->rootName);
    }
    public function setDefaultKey($key) {
        $this->def_key = $key;
    }
    public function convert($data) {
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $key = $this->def_key;
            }
            if (is_array($val)) {
                $this->writer->startElement($key);
                $this->convert($val);
                $this->writer->endElement();
            }
            else {
                $this->writer->writeElement($key, $val);
            }
        }
    }
}

class HTMLRenderer {
    protected static function addRequestParams(&$xslt, $array, $prefix = "") {
      foreach($array as $key => $val) {
        if(!is_array($val)) {
          $xslt->setParameter("", $prefix . $key, $val);
        } else {
          HTMLRenderer::addRequestParams($xslt, $val, $prefix . $key . ".");
        }
      }
    }
    /**
     * Выполнение преобразования, вывод результата (для php5)
     *
     * @param mixed $xsl шаблон 
     * @param array $data данные
     * @param array $params параметры шаблона
     * @return void  
     */
    public static function render($xsl, $data=array(), $params=array()) {
 
        global $registerPHPFunctions;
        // Преобразование данных в xml
        $data2layout_proc = new XMLGenerator;
         $data2layout_proc->setRootName('page');
        $layout = $data2layout_proc->getXML($data);

        // Вызов шаблонизаторa
        $layout2html_proc = new XSLTProcessor;
        $layout2html_proc->importStyleSheet(DOMDocument::load($xsl));
        if ($registerPHPFunctions)
            $layout2html_proc->registerPHPFunctions($registerPHPFunctions);
        HTMLRenderer::addRequestParams($layout2html_proc, $params);
        
        print $layout2html_proc->transformToXML(DOMDocument::loadXML($layout));
    }
}
?>
