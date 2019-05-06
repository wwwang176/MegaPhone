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

$RoomID=trim($_GET['room']);
$LastThreshold=trim($_GET['threshold']);
// echo $LastThreshold;

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

?>