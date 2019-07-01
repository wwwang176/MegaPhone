<?php
session_start();

include __DIR__.'/connect.php';

// requires php5
$img = $_POST['imgBase64'];

if(startsWith($img,'data:image/png;base64,'))
{
    $LastTime=microtime(true);
    $GUID=trim($_POST['guid']);
    $RoomID=trim($_POST['room']);

    // $GUID = uniqid();
    $FileName = $GUID.'.png';
    $FullPath = '../uploads/'.$FileName;
    $RevPath = 'uploads/'.$FileName;

    $img = str_replace('data:image/png;base64,', '', $img);
    $img = str_replace(' ', '+', $img);
    $data = base64_decode($img);
    $file =  $GUID . '.png';
    $success = file_put_contents($FullPath, $data);

    if($success)
    {
        $sql="INSERT INTO `message` SET `guid`=:GUID,`type`=1,`room`=:ROOM,`message`=:MESSAGE,`time`=:TIME,`system`=0,`microtime`=:MTIME ";
        $st=$DBC->prepare($sql);
        $st->execute(array(
            'GUID'=> htmlspecialchars($GUID),
            'ROOM'=> htmlspecialchars($RoomID),
            'MESSAGE'=> $RevPath,
            'TIME'=>date('Y-m-d H:i:s'),
            'MTIME'=>$LastTime
        ));

        echo json_encode(array(
            'state'=>'ok',
            'guid'=>$GUID,
            'FileName'=>$FileName,
            'RevPath'=>$RevPath,
        ));
        exit;
    }
}

echo json_encode(array(
    'state'=>'error'
));
exit;

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

?>