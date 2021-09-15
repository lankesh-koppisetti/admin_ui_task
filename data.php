<?php


header("Content-Type:application/json");

$file_data = file_get_contents("js/members.json");

$jsonData = json_decode($file_data, true);

//==============Functionality for Search===============
if (isset($_GET['action']) && $_GET['action'] == 'search') {

    $searchKey = strtolower($_GET['search_key']);
    $filteredData = [];
    
    foreach ($jsonData as $mem) {
        $name = strtolower($mem['name']);
        $email = strtolower($mem['email']);
        $role = strtolower($mem['role']);

        if ((strpos($name, $searchKey) > -1)) {
            $filteredData[] = $mem;
        }
    }
    $jsonData = $filteredData;
}

//=============edit row=====================

if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    $postInput = $_POST;

    foreach ($jsonData as $mem) {
        $name = strtolower($mem['name']);
        $email = strtolower($mem['email']);
        $role = strtolower($mem['role']);

        if ($postInput['id'] == $mem['id']) {
            $mem['name'] = $postInput['name'];
            $mem['email'] = $postInput['email'];
            $mem['role'] = $postInput['role'];
        }
    }
}


//=============delete row=====================

if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    
    $id = $_POST['id'];
    $position = null;
    
    foreach ($jsonData as $key => $mem) {
        if ($mem['id'] == $id) {
            $position = $key;
        }
    }

    unset($jsonData[$position]);
}

//=============delete seleted rows============

if (isset($_POST['action']) && $_POST['action'] == 'deleteAll') {
   
    $id = $_POST['deleteIds'];
    $ids = explode('|', $id);

    foreach ($jsonData as $key => $mem) {
        $position = null;
        foreach ($ids as $id) {
            if ($mem['id'] == $id) {
                $position = $key;
            }
        }
        unset($jsonData[$position]);
    }
}


$response = [
    "Result" => 'OK',
    "Records" => $jsonData,
    "TotalRecordCount" => count($jsonData)
];

echo json_encode($response);

