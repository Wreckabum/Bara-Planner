<?php
	if(!is_object($account) || empty($account)){
		$account = get_account($_SESSION['id']);
	}
?>
<link href='include/css/bootstrap.min.css' rel='stylesheet' id='bootstrap-css'>
<div class='navbar navbar-expand-md navbar-dark bg-dark mb-4' role='navigation' style='width:100%; z-index:1;'>
    <a class='navbar-brand' href='home.php'>Welcome </a>
    <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarCollapse' aria-controls='navbarCollapse' aria-expanded='false' aria-label='Toggle navigation'>
        <span class='navbar-toggler-icon'></span>
    </button>
    <div class='collapse navbar-collapse' id='navbarCollapse'>
        <ul class='navbar-nav mr-auto'>
            <li class='nav-item'>
				<a class='nav-link'  href='view_account.php?a=<?= $account->sim_id ?>'>
					<?= $account->get_name() ?>
				</a>
            </li>
			<li class='nav-item'>
				<a class='nav-link' href='management.php'>Management</a>
			</li>
        </ul>
		<a class='nav-link' href='logout.php'>Logout</a>
    </div>
</div>