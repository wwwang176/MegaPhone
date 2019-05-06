<?php 
session_start();

include __DIR__.'/connect.php';

$LastTime=microtime(true);

// $UserGUID=trim($_POST['guid']);
$UserGUID=$_SESSION['User.GUID'];
$RoomID=trim($_POST['room']);


$sql="SELECT * FROM `user` WHERE `guid`=:GUID ";
$st=$DBC->prepare($sql);
$st->execute(array(
    'GUID'=>$UserGUID,
));
$Data=$st->fetchAll(PDO::FETCH_ASSOC);

if(count($Data)==0) exit;
$UserName=$Data[0]['name'];

$sql="INSERT INTO `message` SET `guid`=:GUID,`room`=:ROOM,`message`=:MESSAGE,`time`=:TIME,`system`=2,`microtime`=:MTIME ";
$st=$DBC->prepare($sql);
$st->execute(array(
    'GUID'=>MakeGUID(),
    'ROOM'=>$RoomID,
    'MESSAGE'=>json_encode(array(
        'guid'=>$UserGUID,
        'name'=>$UserName,
    )),
    'TIME'=>date('Y-m-d H:i:s'),
    'MTIME'=>$LastTime
));


$sql="DELETE FROM `user` WHERE `guid`=:GUID ";
$st=$DBC->prepare($sql);
$st->execute(array(
    'GUID'=>$UserGUID,
));


function MakeGUID()
{
    $ReturnStr = '';
    $Str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for ($i=0; $i<15; $i++)
    {
        $ReturnStr.=substr($Str, rand(0,strlen($Str)), 1);
    }

    return $ReturnStr;
}