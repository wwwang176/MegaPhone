<?php
// header('Content-Type: text/plain');
//ignore_user_abort
// ignore_user_abort(true);
// @ini_set('zlib.output_compression',0);
// @ini_set('implicit_flush',1);
// @ob_end_clean();

// @apache_setenv('no-gzip', 1);
// apache_setenv('no-gzip', 1);
// @ini_set('zlib.output_compression', 'Off');
// @ini_set('output_buffering', 'Off');
// @ini_set('output_handler', '');
// @apache_setenv('no-gzip', 1);	
// @ob_end_clean();

header("X-Accel-Buffering: no");

// ob_start();

$StartTime=time();

// echo ini_get('max_execution_time'); 

for($i=0;$i<50;$i++)
{
    if(time()-$StartTime>5)
    {
        break;
    }

    echo str_repeat(" ", 1024);
    echo date('Y-m-d H:i:s')."<br>\n";
        
    ob_flush();
    flush();
    
    // usleep(0.5*1000*1000);
    sleep(1);

}

ob_end_flush();

exit;

// flush();
while(true)
{
    // Did the connection fail?
    if(connection_status() != CONNECTION_NORMAL)
    {
        break;
    }

    echo date('Y-m-d H:i:s');
    
    ob_flush();
    flush();

    usleep(0.5*1000*1000);
}

// ob_end_flush();

?>