<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    
</body>
</html>

<script>

var XHRLastText = '';	    //上次接收的訊息
var ChatXHR = new XMLHttpRequest();
ChatXHR.responseType = 'text';
ChatXHR.onreadystatechange = XHRChange;

ChatXHR.open('GET', 'system.php', true);
ChatXHR.send(null);

function XHRChange()
    {
        if (ChatXHR.status == 200)
        {
            if (ChatXHR.readyState == XMLHttpRequest.LOADING)
            {
                // console.log('response',ChatXHR.response);
                //console.log('response',ChatXHR.responseText);

                //去掉先前的訊息內容
                var newText = ChatXHR.responseText.replace(XHRLastText, '');
                XHRLastText = ChatXHR.responseText + '';
                // console.log(newText);

                if (newText == '') return;

                console.log(newText);

                // this can be also binary or your own content type 
                // (Blob and other stuff)
            }

            //當ChatXHR結束
            if (ChatXHR.readyState == XMLHttpRequest.DONE && ChatXHR.status === 200) {
                //console.log('response',ChatXHR.response);
                console.log('DONE');
            }
        }
    }

</script>