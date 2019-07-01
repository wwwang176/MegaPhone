<?php 

if(isset($_FILES['file']) and !$_FILES['file']['error'])
{
    $GUID = MakeGUID();
    $FileName = MakeGUID().'.png';
    $FullPath = dirname(__DIR__).'/uploads/'.$FileName;
    $RevPath = 'uploads/'.$FileName;
    move_uploaded_file($_FILES['file']['tmp_name'], $FullPath);

    echo json_encode(array(
        'state'=>'ok',
        'guid'=>$GUID,
        'FileName'=>$FileName,
        'RevPath'=>$RevPath,
    ));
}
else
{
    echo json_encode(array(
        'state'=>'error'
    ));
}

//建立隨機識別碼
function MakeGUID()
{
    $ReturnStr = '';
    $Str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for ($i = 0; $i < 15; $i++) {
        $ReturnStr .= $Str[floor(rand(0,strlen($Str)))];
    }

    return $ReturnStr;
}

?>