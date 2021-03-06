<?php
class ApplicationControllerGenerator extends CodeGenerator {
	protected $template = '<?php
class ApplicationController extends Controller {

	public function __construct() {
		parent::__construct();
		$this->layout = \'default\';
	}
}

';

	public function __construct() {
		parent::__construct('application');
		$this->path = Config::get('ctrl_dir');
	}

	public function generate() {
		if (!$this->validation()) {
			return;
		}
		$this->generate_path();
		$this->generate_file();

		// make helper
		$generator = new HelperGenerator($this->name);
		$generator->generate();
	}

	public function get_filename() {
		return camel_to_under($this->name) . '_controller.class.php';
	}
}

