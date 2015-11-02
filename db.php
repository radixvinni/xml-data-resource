<?php 
      require_once('stream.php');
      require_once('config.php');

  class dbStream extends BaseStream {
        protected $scheme = "db";
        /**
        *
        * Singleton. Returns valid database connection object.
        *
        * @return posgresql database connection object.
        *
        */
        function db() 
        { 
          global $ekos_host, $ekos_db, $ekos_user, $ekos_pwd, $user_maclabel;
          if(!isset($this->db)) { 
                $this->db =  pg_connect("host=$ekos_host dbname=$ekos_db user=$ekos_user password=$ekos_pwd")
                    or die ("<error>error on connection to $ekos_db</error>");
                if ($user_maclabel)
                    pg_query($this->db,"set ac_session_maclabel = '$user_maclabel';")
                      or die('<error>unable to set session maclabel</error>');

          }
          return $this->db; 
        }
        /**
        *
        * Returns a list of fields in a table.
        *
        * @param string $table - a table.
        *
        * @return array All table columns in the database.
        *
        */
        public function describeTable($table)
        {
          $cmd = "
              SELECT *
              FROM pg_class JOIN pg_attribute ON pg_class.oid = pg_attribute.attrelid
              WHERE pg_class.relname = '{$table}' AND pg_attribute.attnum > 0 
          ";
          $query = pg_query($this->db(), $cmd) or die("<error>unable to fetch column list</error>");
          return pg_fetch_all($query);
        }
        //not tested
        public function getAvailableColumns($table,$maclabel)
        {
          global $user_maclabel;
          if (!$maclabel) $maclabel = $user_maclabel;
          $cmd = "
              SELECT string_agg(pg_attribute.attname,',')
              FROM pg_class JOIN pg_attribute ON pg_class.oid = pg_attribute.attrelid
              WHERE pg_class.relname = '{$table}' AND pg_attribute.attnum > 0 AND pg_attribute.attmaclabel = '{$maclabel}'
          ";
          $query = pg_query($this->db(), $cmd) or die("<error>unable to fetch column list</error>");
          return pg_fetch_result($query,0);
        }
        /**
        *
        * Returns a list of all tables in the database.
        *
        * @param string $schema Fetch tbe list of tables in this schema;
        * when empty, uses the default schema.
        *
        * @return array All table names in the database.
        *
        */
        public function fetchClassList($schema = 'public')
        {
          $cmd = "
              SELECT *
              FROM pg_catalog.pg_class LEFT JOIN pg_catalog.pg_namespace n ON n.oid = relnamespace
              WHERE n.nspname = '{$schema}' AND relkind IN ('r','')
          ";
          $query = pg_query($this->db(), $cmd) or die("<error>unable to fetch table list</error>");
          return pg_fetch_all($query);
        }
        public function fetchTableList($schema = null)
        {
          if ($schema) {
              $cmd = "
                  SELECT table_name
                  FROM information_schema.tables
                  WHERE table_schema = '{$schema}'
              ";
              $values = array('schema' => $schema);
          } else {
              $cmd = "
                  SELECT table_schema || '.' || table_name as table_name
                  FROM information_schema.tables
                  WHERE table_schema != 'pg_catalog'
                  AND table_schema != 'information_schema'
              ";
              $values = array();
          }
          $query = pg_query($this->db(), $cmd) or die("<error>unable to fetch table list</error>");
          return pg_fetch_all($query);
        }
        /* Generates path-specific database query and translates the result to xml.
          db://           - Dump list of tables
          db://table_name - Dump only table table_name
          db://table?option=value
            Options:
              limit=num               - limits the output by num, sets total count to data/@total attribute
              filter[column][]=values - produces filter for column (WHERE column IN (values))
              filter[column]=value    - produces filter for column (WHERE column = value)
              search[]=column         - provides a column names for search (WHERE column like '%q%')
              merge=get               - array_merges $_GET to $this->params(so that filter columns from query string can be accessed)!
              maclabels=...           - select with "maclabel" field
              describe=...            - list columns, no data
              mac=...                 - select only available columns
              
          Requests parameters: merged to $this->params if option merge=get set
            GET:
              s=num                   - when using limit output page number num
              q=text                  - when using search gives query to be searched
        */
        public function stream_open($path, $mode, $options, $opened_path) {
            $path = $this->parsePath($path);

            if($path) {
              if(isset($this->params['describe'])) {
                $this->translateToXml($this->describeTable($path), 'col');
                return true;
              }
              
              if(isset($this->params['merge']))
                $this->params = array_merge_recursive($_GET, $this->params);
              $limit = "";

              if(isset($this->params['filter']) or isset($this->params['search']) ) {
                $where = array();
                foreach($this->params['filter'] as $col => $values)
                  if (is_array($values)) $where[] = " {$col} IN (". implode(",", $values) . ") ";
                  else $where[] = " {$col} = '{$values}' ";
                
                if(isset($this->params['search'])) {
                  $q = pg_escape_string($this->params['q']);
                  $search = array();
                  foreach($this->params['search'] as $col) $search[] = " {$col} LIKE '%{$q}%' ";
                  $where[] = "(". implode(' or ', $search) .")";
                }
                $limit = 'WHERE' . implode(' and ', $where) . $limit;
              }
              if(isset($this->params['limit'])) {
                $this->dom->setAttribute( 'total', pg_fetch_result(pg_query($this->db(),"select count(*) from {$path} {$limit}"), 0) );
                if(isset($this->params['s']))
                  $limit .= " LIMIT {$this->params['limit']} OFFSET ".(intval($this->params['s']) * intval($this->params['limit']));
                else
                  $limit .= " LIMIT {$this->params['limit']}";
                  
              }
              $cols = '*';
              if(isset($this->params['mac'])) $cols = $this->getAvailableColumns($path,$this->params['mac']);
              if(isset($this->params['maclabels'])) $cols .= ',maclabel';
              
              $query = pg_query($this->db(),"SELECT {$cols} FROM {$path} {$limit}");// or die("<error>wrong query $limit on table $path</error>");
              $this->translateToXml(pg_fetch_all($query), $path);
  
            } else {
                if(isset($this->params['all-data']))
                foreach ($this->fetchTableList('public') as $row) {
                    $path = $row['table_name'];
                    $query = pg_query($this->db(),"SELECT * FROM {$path}") or die("<error>table $path does not exist</error>");
                    $this->dom->setDefaultKey($path);
                    $this->dom->convert(pg_fetch_all($query));
                }
                $this->translateToXml($this->fetchClassList('public'), 'table');
            }
            return true;
        }
	};
?>
