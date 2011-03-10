<?php
class ActiveRecord extends Model {
	public $tablename;
	public $columns;

	private $belongs_to_relations = array();
	private $has_one_relations = array();
	private $has_many_relations = array();

	private $query;

	public function __construct($params = array()) {
		parent::__construct($params);
		$this->tablename = classname_to_tablename($this->name);
		$this->query = new Query();
	}

	public function belongs_to($tablename) {
		$active_record = new ActiveRecord();
		$active_record->tablename = $tablename;
		$this->belongs_to_relations[] = $active_record;
	}

	public function has_one($tablename) {
		$active_record = new ActiveRecord();
		$active_record->tablename = $tablename;
		$this->has_one_relations[] = $active_record;
	}

	public function has_many($tablename) {
		$active_record = new ActiveRecord();
		$active_record->tablename = $tablename;
		$this->has_many_relations[] = $active_record;
	}

	public function get_columns() {
		$db = Context::get()->db;
		$this->columns = $db->get_table_columns($this->tablename);
	}

	///////////////////////////////////////////////////////////////////////////
	// DB Query
	///////////////////////////////////////////////////////////////////////////

	public function select($select) {
		$this->query->select($select);
		return $this;
	}

	public function from($from) {
		$this->query->from($from);
		return $this;
	}

	public function join($join, $on = '') {
		$this->query->join($join, $on);
		return $this;
	}

	public function where($where) {
		$this->query->where($where);
		return $this;
	}

	public function group($group, $having ='') {
		$this->query->group($group, $having);
		return $this;
	}

	public function order($order) {
		$this->query->order($order);
		return $this;
	}

	public function limit($param1, $param2= '') {
		$this->query->limit($param1, $param2);
		return $this;
	}

	///////////////////////////////////////////////////////////////////////////
	// new ORM
	///////////////////////////////////////////////////////////////////////////

	/**
	 * @param $option 'first' or 'all'
	 * @return when $option is 'first', then returns object or null.
	 *			when $option is 'all', then returns array.
	 */
	public function find($option = 'first') {
		$db = Context::get()->db;

		// init query
		if (empty($this->query->select)) { $this->query->select = csv($this->get_select_column($this->query->joins)); }
		if (empty($this->query->from)) { $this->query->from = $this->get_select_from($this->query->joins); }
		if ($option == 'first') {
			$this->query->limit = 1;
		}

		// get result
		$result = $db->select($this->query->select, $this->query->from, $this->query->where,
				$this->query->group, $this->query->offset, $this->query->limit, $this->query->order);
		// clean up query object
		$this->query = new Query();

		// return result
		$result = $this->parse_result($result);
		if ($option == 'first') {
			return ((count($result) > 0) ? $result[0] : null);
		}
		return $result;
	}

	///////////////////////////////////////////////////////////////////////////
	// DB Processing
	///////////////////////////////////////////////////////////////////////////

	/**
	 * @return true on success, false on failure
	 */
	public function save() {
		$db = Context::get()->db;

		// load table schema and value setting
		$columns = $db->get_table_columns($this->tablename);
		$names = array();
		$values = array();
		foreach ($columns as $column) {
			$column_name = $column->name;

			if ($column_name == 'id') {
				continue;
			}
			if ($column_name == 'created_at') {
				$this->$column_name = time();
			}
			if ($column_name == 'updated_at') {
				$this->$column_name = time();
			}
			if (!isset($this->$column_name)) {
				continue;
			}

			$names[] = $column_name;
			$values[] = quotes_to_string($column->type, $db->real_escape_string(trim($this->$column_name)));
		}

		// insert
		$result = $db->insert($this->tablename, $names, $values);
		return $result;
	}

	/**
	 * @return int
	 */
	public function count(/* $where = '', $from = '' */) {
		$obj = $this->select('1 id, COUNT(*) as cnt')->find();
		return $obj->cnt;

		/*
		if(is_blank($from)) {
			$from = $this->tablename;
		}

		$result = $db->select('COUNT(*) as cnt', $from, $where);

		while ($row = $db->fetch($result)) {
			$total = $row['cnt'];
		}

		$db->free_result($result);
		return $total;
		*/
	}


	public function update() {
		$db = Context::get()->db;

		// load table schema and value setting
		$columns = $db->get_table_columns($this->tablename);
		$names = array();
		$values = array();
		foreach ($columns as $column) {
			$column_name = $column->name;

			if ($column_name == 'id') {
				continue;
			}
			if ($column_name == 'created_at') {
				continue;
			}
			if ($column_name == 'updated_at') {
				$this->$column_name = time();
			}
			if (!isset($this->$column_name)) {
				continue;
			}

			$names[] = $column_name;
			$values[] = quotes_to_string($column->type, $db->real_escape_string(trim($this->$column_name)));
		}

		// update
		$result = $db->update($this->tablename, $names, $values, 'id = ' . $this->id);
		return $result;
	}

	public function delete($where = '') {
		$db = Context::get()->db;

		// make condition
		if (is_numeric($where)) {
			$where = 'id = ' . $where;
		}
		$result = $db->delete($this->tablename, $where);
		return $result;
	}

	private function get_select_column($include = array()) {
		// init
		$result = array();
		$result = array_merge($result, $this->make_select_column($this));

		foreach($this->belongs_to_relations as $table) {
			if (!in_array($table->tablename, $include)) {
				continue;
			}
			$result = array_merge($result, $this->make_select_column($table));
		}

		foreach($this->has_one_relations as $table) {
			if (!in_array($table->tablename, $include)) {
				continue;
			}
			$result = array_merge($result, $this->make_select_column($table));
		}

		foreach($this->has_many_relations as $table) {
			if (!in_array($table->tablename, $include)) {
				continue;
			}
			$result = array_merge($result, $this->make_select_column($table));
		}

		return $result;
	}

	private function make_select_column($table) {
		$table->get_columns();

		$result = array();
		foreach($table->columns as $column) {
			$result[] = $table->tablename . '.' . $column->name . ' ' . $table->tablename . '__' . $column->name;
		}

		return $result;
	}

	private function get_select_from($include = array()) {
		$from = $this->tablename;

		foreach($this->belongs_to_relations as $table) {
			if (!in_array($table->tablename, $include)) {
				continue;
			}
			$from .= ' join ' . $table->tablename;
			$from .= ' on ' . $table->tablename . '.id = ' . $this->tablename . '.' . $table->tablename . '_id';
		}

		foreach($this->has_one_relations as $table) {
			if (!in_array($table->tablename, $include)) {
				continue;
			}
			$from .= ' join ' . $table->tablename;
			$from .= ' on ' . $table->tablename . '.' . $this->tablename . '_id = ' . $this->tablename . '.id';
		}

		foreach($this->has_many_relations as $table) {
			if (!in_array($table->tablename, $include)) {
				continue;
			}
			$from .= ' left outer join ' . $table->tablename;
			$from .= ' on ' . $table->tablename . '.' . $this->tablename . '_id = ' . $this->tablename . '.id';
		}

		return $from;
	}


	/**
	 * parsing query result and return model object
	 * @param 
	 * @return model object
	 */
	private function parse_result($result) {
		$db = Context::get()->db;

		$arr = array();
		
		while ($row = $db->fetch($result)) {
			$obj = $this->parse_result_row($row, $this->tablename);

			if (isset($old_obj) && $old_obj->id == $obj->id) {
				$obj = $old_obj;
			} else {
				$arr[] = $obj;
			}

			foreach($this->belongs_to_relations as $table) {
				$relation_obj = $this->parse_result_row($row, $table->tablename);
				//if ($relation_obj == null) {
				//	continue;
				//}
				$property = $table->tablename;
				$obj->$property = $relation_obj;
			}

			foreach($this->has_one_relations as $table) {
				$relation_obj = $this->parse_result_row($row, $table->tablename);
				//if ($relation_obj == null) {
				//	continue;
				//}
				$property = $table->tablename;
				$obj->$property = $relation_obj;
			}

			foreach($this->has_many_relations as $table) {
				$relation_obj = $this->parse_result_row($row, $table->tablename);
				$property = $table->tablename;
				if (!isset($obj->$property)) {
					$obj->$property = array();
				}
				if ($relation_obj == null) {
					continue;
				}
				$obj->$property = array_merge($obj->$property, array($relation_obj));
			}

			$old_obj = $obj;
		}
		$db->free_result($result);
		return $arr;
	}

	/**
	 * make result row to object
	 * @param $row - db result row
	 * @param $tablename - table name
	 * @return model object or relation model object
	 */
	private function parse_result_row($row, $tablename) {
		$classname = tablename_to_classname($tablename);
		$obj = new $classname;
		foreach ($row as $temp_column => $value) {
			if (is_int($temp_column)) {
				continue;
			}
			$columns = explode('__', $temp_column);
			// if there is no virtual aliases...
			if (count($columns) < 2) {
				$columns[1] = $columns[0];
				$columns[0] = $this->tablename;
			}
			$tablename2 = $columns[0];
			if ($tablename2 == $tablename) {
				$column = $columns[1];
				$obj->$column = $value;
			}
		}
		if (!isset($obj->id)) {
			return null;
		}
		return $obj;
	}
}

