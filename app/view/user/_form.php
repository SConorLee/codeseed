<div class="control-group">
	<label class="control-label" for="input_email"><i class="icon-check"></i> 이메일</label>
	<div class="controls">
		<input type="email" name="user[email]" id="input_email" placeholder="이메일" value="<?= h(form_value($this, 'user', 'email')) ?>">
	</div>
</div>
<div class="control-group">
	<label class="control-label" for="input_password"><i class="icon-check"></i> 비밀번호</label>
	<div class="controls">
		<input type="password" name="user[password]" id="input_password" placeholder="비밀번호" value="<?= form_value($this, 'user', 'password') ?>">
	</div>
</div>
<div class="control-group">
	<label class="control-label" for="input_repassword"><i class="icon-check"></i> 비밀번호 확인</label>
	<div class="controls">
		<input type="password" name="user[repassword]" id="input_repassword" placeholder="비밀번호 확인" value="<?= form_value($this, 'user', 'repassword') ?>">
	</div>
</div>
<div class="control-group">
	<label class="control-label" for="input_nickname"><i class="icon-check"></i> 별명</label>
	<div class="controls">
		<input type="text" name="user[nickname]" id="input_nickname" placeholder="별명" value="<?= form_value($this, 'user', 'nickname') ?>">
	</div>
</div>

<script type="text/javascript">
function make_nickname() {
	$('#input_nickname').val($('#input_email').val().split('@')[0]);
}

$(function() {
	$('#input_email').keyup(function() {
		$('#input_nickname').val($('#input_email').val().split('@')[0]);
	})
	$('#input_email').blur(function() {
		$('#input_nickname').val($('#input_email').val().split('@')[0]);
	})
});
</script>

