<?php
$root = '/' . ROOT_FOLDER . '/admin';
?>

<nav class="navtop ">
    <div class="desktop py-3 d-none d-lg-flex">
        <h1 class="text-light"><a class="text-reset text-decoration-none h2" href="<?php echo $root .  '/index.php' ?>">Admin</a></h1>
        <a href="<?php echo $root .  '/index.php' ?>"><i class="fas fa-fw fa-database"></i>WODs</a>
        <a href="<?php echo $root .  '/imgen.php' ?>"><i class="fas fa-fw fa-camera"></i>Random</a>
        <a href="<?php echo $root .  '/logs.php' ?>"><i class="fas fa-fw fa-database"></i>Logs</a>
        <a href="<?php echo $root .  '/profile.php' ?>"><i class="fas fa-fw fa-user-circle"></i>Profile</a>
        <a href="<?php echo $root .  '/settings.php' ?>"><i class="fas fa-fw fa-cog"></i>Settings</a>
        <a href="<?php echo $root .  '/logout.php' ?>"><i class="fas fa-fw fa-user-circle"></i>Logout</a>
    </div>
    <div class="mobile mx-3 py-3 d-flex align-items-center d-xl-none d-lg-none h-100">
        <h1 class="text-light"><a class="text-reset text-decoration-none h2" href="<?php echo $root .  '/index.php' ?>">Admin</a></h1>
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
            <i class="fa-solid fa-bars fa-2x text-light"></i>
        </button>
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Administration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                    <li class="nav-item"><a class="nav-link" href="<?php echo $root .  '/index.php' ?>"><i class="me-2 fas fa-fw fa-dumbbell"></i>WODs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $root .  '/imgen.php' ?>"><i class="me-2 fas fa-fw fa-camera"></i>ImGen / Random</a></li>
                    <hr/>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $root .  '/logs.php' ?>"><i class="me-2 fas fa-fw fa-database"></i>Logs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $root .  '/profile.php' ?>"><i class="me-2 fas fa-fw fa-user-circle"></i>Profile</a></li>
                    <hr/>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $root .  '/logout.php' ?>"><i class="me-2 fas fa-sign-out"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>