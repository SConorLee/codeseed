<?php
class LayoutGenerator extends Generator {
	public $path;
	public $template = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="/stylesheets/default.css" media="all" rel="stylesheet" type="text/css" />
<script src="/javascripts/jquery.js" type="text/javascript"></script>
<title><class></title>
</head>
<body>
<div id="contents">
	<?= $CONTENTS ?>
</div>
</div>
</body>
</html>

';

	public function __construct($name, $from = '') {
		parent::__construct($name);
		$this->path = Config::get('layout_dir');
	}

	public function get_contents() {
		$class = under_to_camel($this->name);
		$result = str_replace('<class>', $class, $this->template);
		return $result;
	}
}

