<?php
//ignore_user_abort
// ignore_user_abort(true);
header("X-Accel-Buffering: no");  //https://stackoverflow.com/a/54231709
set_time_limit(30);

$StartTime=time();

include __DIR__.'/connect.php';


// echo 'Testing connection handling in PHP';
// ob_flush();
// flush();

$UserGUID=trim($_GET['user']);
$RoomID=trim($_GET['room']);
$LastThreshold=trim($_GET['threshold']);
// echo $LastThreshold;
$LastTime=microtime(true);


//檢查自己還在不在
$sql="SELECT * FROM `user` WHERE `guid`=:GUID ";
$st=$DBC->prepare($sql);
$st->execute(array(
    'GUID'=>$UserGUID,
));
$UserData=$st->fetchAll(PDO::FETCH_ASSOC);
if(count($UserData)==0)
{
    echo json_encode(array(
        'data'=>array(
            array(
                'message'=>'not-live',
                'system'=>3
            )
        ),
        'threshold'=>$LastThreshold
    ));
    
    ob_flush();
    flush();
}


//更新最後使用時間
$sql="UPDATE `user` SET `time`=:TIME WHERE `guid`=:GUID ";
$st=$DBC->prepare($sql);
$st->execute(array(
    'GUID'=>$UserGUID,
    'TIME'=>date('Y-m-d H:i:s'),
));


//更新使用者最後時間，如果10分鐘則踢出
$sql="SELECT * FROM `user` WHERE `room`=:ROOM AND `time`<:TIME ";
$st=$DBC->prepare($sql);
$st->execute(array(
    'ROOM'=>$RoomID,
    'TIME'=>date('Y-m-d H:i:s',strtotime('-10 minutes')),
));
$AllUser=$st->fetchAll(PDO::FETCH_ASSOC);

//逐一踢出
foreach($AllUser as $User)
{
    $sql="INSERT INTO `message` SET `guid`=:GUID,`room`=:ROOM,`message`=:MESSAGE,`time`=:TIME,`system`=2,`microtime`=:MTIME ";
    $st=$DBC->prepare($sql);
    $st->execute(array(
        'GUID'=>MakeGUID(),
        'ROOM'=>$RoomID,
        'MESSAGE'=>json_encode(array(
            'guid'=>$User['guid'],
            'name'=>$User['name'],
        )),
        'TIME'=>date('Y-m-d H:i:s'),
        'MTIME'=>$LastTime
    ));

    $sql="DELETE FROM `user` WHERE `guid`=:GUID ";
    $st=$DBC->prepare($sql);
    $st->execute(array(
        'GUID'=>$User['guid'],
    ));
}


while(true)
{
    // Did the connection fail?
    if(connection_status() != CONNECTION_NORMAL)
    {
        break;
    }

    if(time()-$StartTime>20)
    {
        break;
    }

    //取得指定時間之後的訊息
    $sql="SELECT `guid`,`message`,`system`,`time`,`microtime` FROM `message` WHERE `room`=:ROOM AND `microtime`>:LASTIME ORDER BY `microtime` ASC, `id` ASC ";
    $st=$DBC->prepare($sql);
    $st->execute(array(
        'ROOM'=>$RoomID,
        'LASTIME'=>$LastThreshold
    ));
    $MessageArray=$st->fetchAll(PDO::FETCH_ASSOC);

    //如果有訊息
    if(count($MessageArray)>0)
    {
        //找到最後時間
        $LastThreshold=$MessageArray[count($MessageArray)-1]['microtime'];

        for($i=0;$i<count($MessageArray);$i++)
        {
            $MessageArray[$i]['time']=strtotime($MessageArray[$i]['time']);
        }
    
        // echo json_encode($MessageArray);
    
        echo json_encode(array(
            'data'=>$MessageArray,
            'threshold'=>$LastThreshold
        ));
        
        ob_flush();
        flush();

    }

    usleep(0.5*1000*1000);
}

// ob_end_flush();

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

?>