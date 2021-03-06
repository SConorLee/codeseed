<?php
class ModelGenerator extends CodeGenerator {
	protected $extention = '.class.php';
	protected $template = '<?php
class <class> extends ActiveRecord {

}

';

	public function __construct($name, $from = '') {
		parent::__construct($name);
		$this->path = Config::get('model_dir');
	}

	public function generate() {
		if (!$this->validation()) {
			return;
		}
		$this->generate_path();
		$this->generate_file();

		$migration_generator = new MigrationGenerator($this->name, 'model');
		$migration_generator->generate();
	}

	public function get_contents() {
		$class = under_to_camel($this->name);
		$result = str_replace('<class>', $class, $this->template);
		return $result;
	}
}

