<?php
class BlogController extends Controller {

	public function __construct() {
		$this->layout = 'blog';
	}

	public function post_form() {
	}

	public function post() {
		global $params;
		global $flash;

		$article = new Article($params['article']);
		if ($article->validate()) {
			$article->save();
			$this->redirect_to('/blog/index');
		} else {
			$flash->add('message', $article->errors->get_messages());
			$this->back();
		}
	}

	public function index($page = '1') {
		$article = new Article();
		$page_size = 7;
		$this->list = $article->find_all(array('page' => $page, 'size' => $page_size, 'order' => 'id DESC'));
		$this->paging = new Paging($article->count(), $page_size, '/blog/index/<page>', $page);

		$comment = new ArticleComment();
		$article_ids = $this->get_article_ids($this->list);
		$article_ids = implode(', ', $article_ids);
		$this->comment_counts = $comment->find_all(array('select' => 'article_id, count(*) cnt', 'where' => "article_id in ($article_ids)", 'group' => 'article_id'));
	}

	private function get_article_ids($list) {
		$result = array(0);
		foreach($list as $article) {
			$result[] = $article->id;
		}
		return $result;
	}

	public function view($id, $page = '1') {
		$article = new Article();
		$this->article = $article->find("id = '$id'");

		$comment = new ArticleComment();
		$this->comment_list = $comment->find_all(array('where' => "article_id = '$id'"));
	}

	public function update_form($id) {
		$article = new Article();
		$this->article = $article->find("id = '$id'");
	}

	public function update() {
		global $params;
		global $flash;

		$article = new Article($params['article']);
		if ($article->validate()) {
			$article->update();
			$this->redirect_to('/blog/index');
		} else {
			$flash->add('message', $article->errors->get_messages());
			$this->back();
		}
	}

	public function delete($id) {
		$article = new Article();
		$article->delete('id = ' . $id);

		$comment = new ArticleComment();
		$comment->delete('article_id = ' . $id);

		$this->redirect_to('/blog/index');
	}

	public function post_comment() {
		global $flash;
		global $params;

		$comment = new ArticleComment($params['article_comment']);
		if ($comment->validate()) {
			$comment->save();
		} else {
			$flash->add('message', $comment->errors->get_messages());
		}
		$this->redirect_to('/blog/view/' . $params['article_comment']['article_id']);
	}
}

