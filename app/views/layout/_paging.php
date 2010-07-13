<div class="paging">
<?php
$pages = $this->paging->pages;
if (!$this->paging->is_first_frame) {
	$link = $this->paging->get_link(1);
	echo '[<a href=\'' . $link . '\'>FIRST</a>]';
	$link = $this->paging->get_link($pages[0] - 1);
	echo '[<a href=\'' . $link . '\'>PREV</a>]';
}
foreach ($pages as $page) {
	if ($this->paging->cur_page == $page) {
		echo '[<b>' . $page . '</b>]';
	} else {
		$link = $this->paging->get_link($page);
		echo '[<a href=\'' . $link . '\'>' . $page . '</a>]';
	}
}
if (!$this->paging->is_last_frame) {
	$link = $this->paging->get_link(array_pop($pages) + 1);
	echo '[<a href=\'' . $link . '\'>NEXT</a>]';
	$link = $this->paging->get_link($this->paging->get_last_page());
	echo '[<a href=\'' . $link . '\'>LAST</a>]';
}
?>
</div>

