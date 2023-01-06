<?php
    $root = '/workouts/admin';
?>

<nav class="navtop">
    <div>
        <h1>WOD Manager - Admin</h1>
        <a href="<?php echo $root .  '/index.php' ?>"><i class="fas fa-fw fa-database"></i>WODs</a>
        <a href="<?php echo $root .  '/imgen.php' ?>"><i class="fas fa-fw fa-camera"></i>Random</a>
        <a href="<?php echo $root .  '/logs.php' ?>"><i class="fas fa-fw fa-database"></i>Logs</a>
        <a href="<?php echo $root .  '/profile.php' ?>"><i class="fas fa-fw fa-user-circle"></i>Profile</a>
        <a href="<?php echo $root .  '/logout.php' ?>"><i class="fas fa-fw fa-user-circle"></i>Logout</a>
    </div>
</nav>