<?php
	if(!is_object($account)){
		$account = get_account($_SESSION["id"]);
	}
?>
<div style='float:left;'>
	Welcome 
	<a href='#'>
		<a href='view_account.php?a=<?= $account->id ?>'>
			<?= $account->get_full_name() ?>
		</a>
	</a>!
	<?php
		if($account->is_admin()){
	?>
			<a href='add_student.php'>
				[ Add a new student ]
			</a>
			<a href='add_faculty.php'>
				[ Add a new faculty member ]
			</a>
			<a href='add_major.php'>
				[ Add a new major ]
			</a>
			<a href='add_project.php'>
				[ Add a new project ]
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