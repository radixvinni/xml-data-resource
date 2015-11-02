# xml-data-resource
A data is imported from a XML file with format:

```xml
<?xml version="1.0"?>
<data>
  <table_name>
    <column_name>value</column_name>
  </table_name>

  <test>
    <id>1</id>
    <name>a</name>
  </test>  
  <test>
    <id>2</id>
    <name>b</name>
  </test>  
</data>
```

The resource engine creates a Postgresql DB schema from this file and stores the given records. Relaunching an import will create all tables from scratch. Stored data can be listed, searched by string fields, filtered by numeric fields and shown through XSL transformation described in <code>xsl/data.xsl</code> or transformed and loaded to another DB through XSL transformation in <code>xsl/cbd.xsl</code>
