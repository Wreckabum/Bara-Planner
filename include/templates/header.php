<?php
	if(!is_object($account)){
		$account = get_account($_SESSION["id"]);
	}
?>
<div style='float:left;'>
	Welcome 
	<a href='#'>
		<a href='#'>
			<?= $account->get_full_name() ?>
		</a>
	</a>!
	<?php
		if($account->is_admin()){
	?>
			<a href='add_student.php'>
				[ Add a new student ]
			</a>
	<?php
		}
	?>
</div>
<div style='float:right;'>
	<a href='logout.php'>
		Logout
	</a>
</div>
<br />
<br />