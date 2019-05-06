<?php 
session_start();

include __DIR__.'/connect.php';

$LastTime=microtime(true);
define('MessageSaveTimeMax',10);     //伺服器保留訊息時間(秒)

$GUID=trim($_POST['guid']);
$Message=trim($_POST['message']);
$RoomID=trim($_POST['room']);

$sql="INSERT INTO `message` SET `guid`=:GUID,`room`=:ROOM,`message`=:MESSAGE,`time`=:TIME,`system`=0,`microtime`=:MTIME ";
$st=$DBC->prepare($sql);
$st->execute(array(
    'GUID'=> htmlspecialchars($GUID),
    'ROOM'=> htmlspecialchars($RoomID),
    'MESSAGE'=> htmlspecialchars($Message),
    'TIME'=>date('Y-m-d H:i:s'),
    'MTIME'=>$LastTime
));

//刪除超過時間的訊息
$sql="DELETE FROM `message` WHERE `microtime`<:MTIME ";
$st=$DBC->prepare($sql);
$st->execute(array(
    'MTIME'=>($LastTime-MessageSaveTimeMax)
));