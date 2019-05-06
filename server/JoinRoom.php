<?php 
session_start();

include __DIR__.'/connect.php';

$LastTime=microtime(true);


$UserName=trim(htmlspecialchars($_POST['name']));
$RoomID=trim(htmlspecialchars($_POST['room']));
$_SESSION['User.RoomID']=$RoomID;
$UserGUID='';

//建立使用者不重複GUID
while(true)
{
    $UserGUID=MakeGUID();
    $_SESSION['User.GUID']=$UserGUID;

    $sql="SELECT `guid` FROM `user` WHERE `guid`=:GUID ";
    $st=$DBC->prepare($sql);
    $st->execute(array(
        'GUID'=>$UserGUID,
    ));
    $Data=$st->fetchAll(PDO::FETCH_ASSOC);

    if(count($Data)==0)
        break;
}

//建立加入訊息
$sql="INSERT INTO `message` SET `guid`=:GUID,`room`=:ROOM,`message`=:MESSAGE,`time`=:TIME,`system`=1,`microtime`=:MTIME ";
$st=$DBC->prepare($sql);
$st->execute(array(
    'GUID'=>$UserGUID,
    'ROOM'=>$RoomID,
    'MESSAGE'=>json_encode(array(
        'guid'=>$UserGUID,
        'name'=>$UserName,
    )),
    'TIME'=>date('Y-m-d H:i:s'),
    'MTIME'=>$LastTime
));

//建立成員
$sql="INSERT INTO `user` SET `guid`=:GUID,`room`=:ROOM,`name`=:NAME ";
$st=$DBC->prepare($sql);
$st->execute(array(
    'GUID'=>$UserGUID,
    'ROOM'=>$RoomID,
    'NAME'=>$UserName,
));


//取得房內所有成員
$sql="SELECT * FROM `user` WHERE `room`=:ROOM ";
$st=$DBC->prepare($sql);
$st->execute(array(
    'ROOM'=>$RoomID,
));
$ThisRoomMemberData=$st->fetchAll(PDO::FETCH_ASSOC);


echo json_encode(array(
    'guid'=>$UserGUID,
    'existmember'=>$ThisRoomMemberData
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