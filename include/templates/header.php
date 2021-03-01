<?php
	if(!is_object($account)){
		$account = get_account($_SESSION["id"]);
	}
?>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<style>
.navbar .dropdown-toggle, .navbar .dropdown-menu a {
    cursor: pointer;
}

.navbar .dropdown-item.active, .navbar .dropdown-item:active {
    color: inherit;
    text-decoration: none;
    background-color: inherit;
}

.navbar .dropdown-item:focus, .navbar .dropdown-item:hover {
    color: #16181b;
    text-decoration: none;
    background-color: #f8f9fa;
}

@media (min-width: 767px) {
    .navbar .dropdown-toggle:not(.nav-link)::after {
        display: inline-block;
        width: 0;
        height: 0;
        margin-left: .5em;
        vertical-align: 0;
        border-bottom: .3em solid transparent;
        border-top: .3em solid transparent;
        border-left: .3em solid;
    }
}
</style>

<div class="navbar navbar-expand-md navbar-dark bg-dark mb-4" role="navigation">
    <a class="navbar-brand" href="home.php">Welcome </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
				<a class="nav-link"  href='view_account.php?a=<?= $account->id ?>'>
					<?= $account->get_name() ?>
				</a>
            </li>
			<?php
				if(!$account->is_student()){
			?>
					<li class="nav-item">
						<a class="nav-link" href="view_all.php">View All</a>
					</li>
			<?php
				}
				
				if($account->is_student() || $account->is_faculty()){
					$s = ($account->is_faculty() ? "s" : "");
			?>
					<li class="nav-item">
						<a class="nav-link" href="view_group.php">View Group<?= $s ?></a>
					</li>
			<?php
				}
				
				if($account->is_admin()){
			?>
					<li class="nav-item">
						<a class="nav-link" href="add_student.php">Add a new student</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="add_faculty.php">Add a new faculty member</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="add_major.php">Add a new major</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="add_project.php">Add a new project</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="import.php">Import</a>
					</li>
			<?php
				}
				
				if($account->is_super()){
			?>
					<li class="nav-item">
						<a class="nav-link" href="add_admin.php">Add an administrator</a>
					</li>
			<?php
				}
			?>
        </ul>
		<a class="nav-link" href="logout.php">Logout</a>
    </div>
</div>
<!-- <div style='float:left;'>
	Welcome 
	<a href='#'>
		<a href='view_account.php?a=<?= $account->id ?>'>
			<?= $account->get_name() ?>
		</a>
	</a>!
	<a href='view_all.php'>
		[ View All ]
	</a>
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
			<a href='import.php'>
				[ Import file ]
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
<br /> -->