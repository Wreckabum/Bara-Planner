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
.navbar{
	z-index:1;
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
				<a class="nav-link"  href='view_account.php?a=<?= $account->sim_id ?>'>
					<?= $account->get_name() ?>
				</a>
            </li>
			<?php
				if($account->is_student()){
			?>
					<li class="nav-item">
						<a class="nav-link"  href='view_choices.php'>
							View Choices
						</a>
					</li>
			<?php
				}
			?>
			<li class="nav-item">
				<a class="nav-link" href="management.php">Management</a>
			</li>
			
        </ul>
		<a class="nav-link" href="logout.php">Logout</a>
    </div>
</div>