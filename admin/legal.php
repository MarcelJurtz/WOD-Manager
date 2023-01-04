<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: index.html');
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Home Page</title>
    <link href="admin.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="./../vendor/fontawesome-free-5.15.4-web/css/all.min.css">
    <link rel="stylesheet" href="./../assets/css/bootstrap-5.0.0-beta3/bootstrap.min.css">
</head>

<body class="loggedin">
    <script src="./../assets/js/bootstrap-5.0.0-beta3/bootstrap.bundle.min.js"></script>
    <nav class="navtop">
        <?php
        include('./menu.php');
        ?>
    </nav>
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-8">
                    <h2>Legal</h2>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<?php
function RenderModal($id, $caption, $contentFileName)
{
    $modalId = "modal-" . $id;
    $modalLabelId = "lblModal-" . $id;
    $content = file_get_contents(__DIR__.$contentFileName);
    // TODO: Modal styling ueber bootstrap theme
    print('<div class="modal fade" id="'.$modalId.'" tabindex="-1" aria-labelledby="'.$modalLabelId.'" aria-hidden="true">
            <div class="modal-dialog" style="max-width:60%">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="'.$modalLabelId.'">'.$caption.'</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        '.$content.'
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>');
}
?>