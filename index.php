<?php 
session_start();
date_default_timezone_set('Asia/Taipei');

include __DIR__.'/server/connect.php';
/*
$sql="SELECT * FROM `user` WHERE `guid`=:GUID ";
$st=$DBC->prepare($sql);
$st->execute(array(
	'GUID'=>$_SESSION['User.GUID'],
));
$UserData=$st->fetchAll(PDO::FETCH_ASSOC);

if(count($UserData)>0)
{
	$UserData=$UserData[0];
}*/

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/home.css">
    <title>傳聲筒</title>
    <script src="js/jquery-2.2.4.min.js"></script>
	<script src="js/moment.js"></script>
	<script src="js/MegaChat.js"></script>
	<script src="js/Autolinker.min.js"></script>
	<!-- <script src="js/paste.js"></script> -->
</head>
<body>

	<div class="safeMask" style="display:none;"></div>

	<!-- 開啟通知提醒 -->
	<!-- <div class="plzOpenNotification">
		<div class="shadow"></div>
		<div class="text">
			<span>請開啟通知</span>
		</div>
	</div> -->

	<!-- 登入 -->
	<div class="loginWrap dialog">
		<div class="loginArea">
			<div class="logo">
				<img src="images/logo.png" alt="">
			</div>
			<form action="">
				<input class="room" type="text" placeholder="房間號碼" value="<?php echo htmlspecialchars($UserData['room']);?>">
				<input class="name" type="text" placeholder="暱稱" value="<?php echo htmlspecialchars($UserData['name']);?>">
				<input type="submit" class="btn" value="建立 / 進入">
			</form>
		</div>
	</div>

    <div class="chatWrap blur">
        <div class="funcArea">
            <div class="logo item">
				<div class="inner">
					<img src="images/logo.png" alt="">
				</div>
			</div>
			<div class="delay item">
				<div class="inner">
					300ms
				</div>
			</div>
			
        </div>
		<div class="roomArea">
			<div class="roonInfoArea">
				1506
			</div>
			<div class="memberArea">
				<!-- <div class="member">
					<div class="image">
					</div>
					<div class="name">匿名熊貓</div>
				</div>
				<div class="member">
					<div class="image">
					</div>
					<div class="name">匿名熊貓</div>
				</div>
				<div class="member">
					<div class="image">
					</div>
					<div class="name">匿名熊貓</div>
				</div>
				<div class="member">
					<div class="image">
					</div>
					<div class="name">匿名熊貓</div>
				</div> -->
			</div>
		</div>
        <div class="chatArea">
			<div class="systemStateArea re-connect">
				重新連線中
			</div>
            <div class="messageArea">
                <div class="outer">
					<div class="inner">
						<div class="inner2">
							<!-- <div class="message system">
								<div class="text"><div class="body">王小名 加入房間</div></div>
								<div class="time">04:56</div>
							</div>
							<div class="message system">
								<div class="text"><div class="body">王小名 離開入房間</div></div>
								<div class="time">04:56</div>
							</div>
							<div class="message">
								<div class="text"><div class="body">測試看看</div></div>
								<div class="time">04:56</div>
							</div>
							<div class="message right">
								<div class="name">匿名熊貓</div>
								<div class="text">
									<div class="body">測試看看2</div>
									<div class="loading"></div>
								</div>
								<div class="time">04:56</div>
							</div>
							<div class="message">
								<div class="text"><div class="body">測試看看3</div></div>
								<div class="time">04:56</div>
							</div> -->
						</div>
					</div>
                </div>
            </div>
            <div class="sendArea">
                <input type="text" class="text" placeholder="輸入秘密訊息...">
                <div class="send">發送</div>
            </div>
        </div>
    </div>
</body>
</html>

<script>

	
var Chat=null;
var LastNotifyID;
var CanNotify=false;		//是否可以通知使用者
var CanTitleNotify=false;		//是否可以標題閃爍通知使用者
var UnReadMessageCount=0;		//未讀訊息

// request permission on page load
document.addEventListener('DOMContentLoaded', function () {
  if (!Notification) {
    alert('請使用chrome瀏覽器開啟'); 
    return;
  }

  if (Notification.permission !== "granted")
  	RequestNotificationPermission();
});


function Notify(Message, CloseTime) {

	UnReadMessageCount++;

	if (Notification.permission !== "granted")
	{
		RequestNotificationPermission();
	}
	else
	{
		if(CanNotify)
		{
			var MessageBody=Message || "收到新訊息";
			var CloseTime = CloseTime || 1000;
	
			var LastNotifyID = new Notification('傳聲筒', {
				icon: 'images/logo.png',
				body: MessageBody,
			});
	
			setTimeout(function(){
				LastNotifyID.close();
			}, CloseTime); 
	
			// LastNotifyID.onclick = function () {
			//   window.open("http://stackoverflow.com/a/13328397/1269037");      
			// };
		}
	}

}

var TitleNotifySetTimer;
var TitleNotifyShake=false;
function TitleNotify()
{
	clearTimeout(TitleNotifySetTimer);

	if(UnReadMessageCount>0)
	{
		TitleNotifySetTimer=setTimeout(function(){
			
			TitleNotifyShake=!TitleNotifyShake;
			TitleNotify();

		}, 1000);

		if(CanTitleNotify && TitleNotifyShake)
		{
			document.title=UnReadMessageCount+' 則新訊息 - 傳聲筒';
		}
		else
		{
			TitleNotifyReset();
		}
	}
	else
	{
		TitleNotifyReset();
	}
	
}
function TitleNotifyReset()
{
	document.title='傳聲筒';
}

function RequestNotificationPermission()
{
	Notification.requestPermission(function(state){
		
	});

	/*
	$('.plzOpenNotification').show();
	$('.loginWrap').addClass('blur');
	Notification.requestPermission(function(state){
		if (state == "granted")
		{
			$('.plzOpenNotification').hide();
			$('.loginWrap').removeClass('blur');
		}
		else
		{
			$('.plzOpenNotification .shadow').hide();
		}
	});*/
}


//傳送訊息
function SendMessageCallBack()
{
	$('.sendArea input.text').val('');
}

$(function(){

	$(window).on('keydown click',function(e){

		$('.safeMask').hide();

		if(Chat!=null)
			$('.sendArea input.text').focus();
	});

	$('.sendArea input.text').on('keydown',function(e){

		if (e.keyCode == 13)
		{
			var Text = $.trim($(this).val());
			Chat.SendMessage(Text);
		}
	});

	$(window).on('focus',function(){
		CanNotify=false;
		CanTitleNotify=false;
		UnReadMessageCount=0;
		TitleNotifyReset();
	});
	$(window).on('blur',function(){
		CanNotify=true;
		CanTitleNotify=true;
		$('.safeMask').show();
	});
	

});

//隨機名稱
var TitleName=["秘密的","神秘的","可疑的","不願露臉的","隱姓埋名的","未知的","低調的","遮遮掩掩的"];
var AnimalName=["野馬","水牛","山羊","羚羊","斑馬","長頸鹿","馴鹿","駱駝","大象","犀牛","河馬","獅子","美洲豹","老虎","野貓","黃鼠狼","鬣狗","狼","松鼠","土撥鼠","雪貂","熊","兔子","野兔","土撥鼠","老鼠","田鼠","猴子","黑猩猩","長臂猿","食蟻獸","鴨嘴獸","袋鼠","刺蝟","豪豬","蝙蝠","鯨魚","河豚","海豹","老鷹","禿鷹","公雞","火雞","孔雀","野鴨","天鵝","野鴿","斑鳩","信天翁","海鷗","啄木鳥","鸚鵡","麻雀","燕子","企鵝","貓頭鷹","眼鏡蛇","響尾蛇","蜥蜴","變色龍","鱷魚","美洲鱷","海龜","青蛙","蟾蜍"];
$('.loginWrap input.name').val(TitleName[Math.floor(Math.random()*TitleName.length)]+AnimalName[Math.floor(Math.random()*AnimalName.length)]);
$('.loginWrap input.room').focus();

$('.loginWrap form').on('submit',function(e){
	e.preventDefault();

	var RoomId=$('.loginWrap input.room').val();
	var UserName=$('.loginWrap input.name').val();

	Chat=new MegaChat({
		UserName: UserName,
		RoomID: RoomId,
		ChatUrl: 'server/system.php',
		// TimeThreshold: '<?php echo urlencode(microtime(true));?>',
		TimeThreshold: Math.floor(new Date().getTime()/1000),
		SendMessageCallBack: SendMessageCallBack
	});

	$('.roonInfoArea').text(RoomId);

	$('.loginWrap').hide();
	$('.chatWrap').removeClass('blur');
	$('.sendArea input[type="text"]').focus();

	$(window).bind('beforeunload', function () {
		Chat.LeaveRoom();
	});	

	/*window.addEventListener("paste", function(e){
		// Handle the event
		retrieveImageFromClipboardAsBlob(e, function(imageBlob){
			// If there's an image, display it in the canvas
			if(imageBlob){

				var data = new FormData();
				data.append('file', imageBlob);
				
				$.ajax({
					url :  "server/upload.php",
					type: 'POST',
					data: data,
					contentType: false,
					processData: false,
					dataType: 'json',
					success: function(data) {
						alert("boa!");
						console.log(data);
					},    
					error: function() {
						alert("not so boa!");
					}
				});
			}
		});
	}, false);*/
});

// $('.messageArea .inner').on('scroll',function(){
// 	console.log($('.messageArea .inner').scrollTop() + $('.messageArea').height(), $('.messageArea .inner2').height())

// })


/**
 * This handler retrieves the images from the clipboard as a blob and returns it in a callback.
 * 
 * @see http://ourcodeworld.com/articles/read/491/how-to-retrieve-images-from-the-clipboard-with-javascript-in-the-browser
 * @param pasteEvent 
 * @param callback 
 */
function retrieveImageFromClipboardAsBlob(pasteEvent, callback){
	if(pasteEvent.clipboardData == false){
        if(typeof(callback) == "function"){
            callback(undefined);
        }
    };

    var items = pasteEvent.clipboardData.items;

    if(items == undefined){
        if(typeof(callback) == "function"){
            callback(undefined);
        }
    };

    for (var i = 0; i < items.length; i++) {
        // Skip content if not image
        if (items[i].type.indexOf("image") == -1) continue;
        // Retrieve image on clipboard as blob
        var blob = items[i].getAsFile();

        if(typeof(callback) == "function"){
            callback(blob);
        }
    }
}

/**
 * This handler retrieves the images from the clipboard as a base64 string and returns it in a callback.
 * 
 * @param pasteEvent 
 * @param callback 
 */
function retrieveImageFromClipboardAsBase64(pasteEvent, callback, imageFormat){
	if(pasteEvent.clipboardData == false){
        if(typeof(callback) == "function"){
            callback(undefined);
        }
    };

    var items = pasteEvent.clipboardData.items;

    if(items == undefined){
        if(typeof(callback) == "function"){
            callback(undefined);
        }
    };

    for (var i = 0; i < items.length; i++) {
        // Skip content if not image
        if (items[i].type.indexOf("image") == -1) continue;
        // Retrieve image on clipboard as blob
        var blob = items[i].getAsFile();

        // Create an abstract canvas and get context
        var mycanvas = document.createElement("canvas");
        var ctx = mycanvas.getContext('2d');
        
        // Create an image
        var img = new Image();

        // Once the image loads, render the img on the canvas
        img.onload = function(){
			// Update dimensions of the canvas with the dimensions of the image

			//縮放到最大600*400
			var PerW = 600 / this.width;
			var PerH = 400 / this.height;
			var Per = Math.min(PerW,PerH);

			if(Per>1)Per=1;

            mycanvas.width = this.width *Per;
            mycanvas.height = this.height *Per;

            // Draw the image
            ctx.drawImage(img, 0, 0, this.width*Per, this.height*Per);

            // Execute callback with the base64 URI of the image
            if(typeof(callback) == "function"){
                callback(mycanvas.toDataURL(
                    (imageFormat || "image/png")
                ));
            }
        };

        // Crossbrowser support for URL
        var URLObj = window.URL || window.webkitURL;

        // Creates a DOMString containing a URL representing the object given in the parameter
        // namely the original Blob
        img.src = URLObj.createObjectURL(blob);
    }
}

</script>