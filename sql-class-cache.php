<?php

class SqlClassCache extends SqlClassCommon {

	const   MINUTE = 60;
	const   HOUR = 3600;
	const   DAY = 86400;

	public  $sql;           # A reference to an sql class instance to execute the query over

	public  $query;         # The query to be executed
	public  $params;        # The parameters to be used in the query
	public  $hash;          # The hash key of the query

	public  $dir;           # The location of the cache storage

	public  $totalRows;     # The total number of rows
	public  $nextRow;       # The number of the next row to fetch

	public  $timeout;       # How long the data should be cached for
	public  $cacheTime;     # The time that the data was cached at


	public function __construct($options=false) {

		$options = $this->getOptions($options,array(
			"dir"           =>  "/tmp/query_cache",
			"sql"           =>  false,
			"query"         =>  false,
			"params"        =>  false,
			"timeout"       =>  self::DAY,
		));

		$this->sql = $options["sql"];

		# Store the query for other methods to use
		$this->query = $options["query"];
		$this->params = $options["params"];

		# Create the hash of the query to use as an identifier
		$this->hash = sha1($this->query . print_r($this->params,1));

		$this->dir = $options["dir"] . "/" . $this->hash;

		$this->timeout = $options["timeout"];

		# Ensure a cache directory exists for this query
		if(!is_dir($this->dir)) {
			mkdir($this->dir,0775,true);
		}

		# If cache doesn't exist for this query then create it now
		if(!$this->isCached()) {
			$this->createCache();
		}

		$this->totalRows = file_get_contents($this->dir . "/.status");
		$this->nextRow = 0;

	}


	public function isCached() {

		if($this->sql->output) {
			echo "checking the cache (" . $this->dir . ")";
			echo ($this->sql->htmlMode) ? "<br>" : "\n";
		}

		# If no status file exists for this cache then presume there isn't any valid data
		if(!file_exists($this->dir . "/.status")) {
			if($this->sql->output) {
				echo "no status file found";
				echo ($this->sql->htmlMode) ? "<hr>" : "\n";
			}
			return false;
		}

		# If the status file is older than the specified timeout then force a refresh
		$this->cacheTime = filectime($this->dir . "/.status");
		if($this->sql->output) {
			echo "cache found (" . date("d/m/y H:i:s",$this->cacheTime) . ")";
		}

		if($this->cacheTime < (time() - $this->timeout)) {
			if($this->sql->output) {
				echo ($this->sql->htmlMode) ? "<br>" : "\n";
				echo "cache is too old to use";
			}
			return false;
		}

		if($this->sql->output) {
			echo ($this->sql->htmlMode) ? "<hr>" : "\n";
		}

		return true;

	}


	public function createCache() {

		$this->result = $this->sql->query($this->query,$this->params);

		$this->cacheTime = time();

		file_put_contents($this->dir . "/.status","0");

		$rowNum = 0;
		while($row = $this->sql->fetch($this->result)) {

			$json = json_encode($row);

			file_put_contents($this->dir . "/" . $rowNum . ".row",$json);

			$rowNum++;

		}

		file_put_contents($this->dir . "/.status",$rowNum);

	}


	public function fetch($indexed=false) {

		if($this->nextRow >= $this->totalRows) {
			return false;
		}

		$rowIndex = $this->nextRow;

		$json = file_get_contents($this->dir . "/" . $rowIndex . ".row");

		$row = json_decode($json,true);

		$this->nextRow++;

		if($indexed) {
			$new = array();
			foreach($row as $val) {
				$new[] = $val;
			}
			$row = $new;
		}

		return $row;

	}


	public function seek($row) {

		$this->nextRow = $row;

	}


	public function result($row,$col) {

		$this->seek($row);

		$row = $this->fetch(true);

		$val = $row[$col];

		return $val;

	}


}