<?php

    require_once($_SERVER['DOCUMENT_ROOT'] . '/workouts/admin/config.php');

    $con = getConnection();
    $uid = null;

    logApiAccess($con);

    if(isset($_GET['id'])) {
        $uid = $_GET['id'];
    }

    header("Content-Type: application/json");

    $wod = getWod($con, $uid);
    if(!$wod) {
        http_response_code(404);
        exit();
    }

    http_response_code(200);
    echo json_encode($wod);
    exit();

    function getConnection() {        
        $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $con->set_charset("utf8mb4");
        if (mysqli_connect_errno()) {
            exit('Failed to connect to MySQL: ' . mysqli_connect_error());
        }

        return $con;
    }

    function getWod($con, $uid) {

        $id = 0;
        $designation = '';
        $description = '';
        $exercises = '';
        $permalink = '';

        if($uid == null) {
            // get random wod
            $stmt = $con->prepare('SELECT id, designation, description, exercises, permalink FROM wod ORDER BY RAND() LIMIT 1;');
            $stmt->execute();
            $stmt->bind_result($id, $designation, $description, $exercises, $permalink);
            $stmt->fetch();
            $stmt->close();
        } else {
            // get wod by permalink
            $stmt = $con->prepare('SELECT id, designation, description, exercises, permalink FROM wod WHERE permalink = ?');
            $stmt->bind_param('s', $uid);
            $stmt->execute();
            $stmt->bind_result($id, $designation, $description, $exercises, $permalink);
            $stmt->fetch();
            $stmt->close();
        }

        if($id == 0) {
            return false;
        }

        // Get tags & referenced tags for current workout
        $stmt = $con->prepare('SELECT tag.designation FROM tag JOIN wod_tag ON tag.id = wod_tag.tag_id WHERE wod_tag.tag_id = tag.id AND wod_tag.wod_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $tags = array();
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }

        $stmt->free_result();
        $stmt->close();

        $stmt = $con->prepare('SELECT equipment.designation, equipment.displayname FROM equipment JOIN wod_equipment ON equipment.id = wod_equipment.equipment_id WHERE wod_equipment.equipment_id = equipment.id AND wod_equipment.wod_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $equipment = array();
        while ($row = $result->fetch_assoc()) {
            $equipment[] = $row;
        }

        $stmt->free_result();
        $stmt->close();

        $data = array(
            'designation' => $designation,
            'description' => $description,
            'exercises' => explode(',',$exercises),
            'permalink' => $permalink,
            'equipment' => $equipment,
            'tags' => $tags
          );

        return $data;
    }

    function getWodByEquipment($equipment) {
        // TODO
    }

    function logApiAccess($con) {
        $stmt = $con->prepare("INSERT INTO log (source, ip, params) VALUES ('api',?,?)");

        $ip = $_SERVER['REMOTE_ADDR'];
        $url = basename($_SERVER['REQUEST_URI']);

        $stmt->bind_param('ss', $ip, $url);
        $stmt->execute();
        $stmt->close();
    }
?>