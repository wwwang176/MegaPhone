
function MegaChat(NewConfig)
{
    //設定
    var Config={
        UserName: '',                   //使用者暱稱
        UserGUID: '',                   //使用者識別碼
        RoomID: '',                     //房號
        ChatUrl:'',                     //系統位置
        SystemDisplayMessageMax: 20,    //畫面上最多顯示幾則訊息
        TimeThreshold: 0,
        SendMessageCallBack: undefined
    };  
    Config = $.extend(Config, NewConfig);

    var ThisChat=this;
    var messageDisplayBox;      //訊息顯示位置
    var ChatXHR=null;           //XHR
    var XHRLastText = '';	    //上次接收的訊息
    var XHRLastTimeThreshold = Config.TimeThreshold;   //上次接收到的訊息中 最後時間
    var SendGUID = [];          //發送紀錄
    var ReceiveGUID = [];       //接收紀錄
    var SystemDelay=0.0;        //系統延遲(毫秒)

    ChatXHR = new XMLHttpRequest();
    ChatXHR.responseType = 'text';
    ChatXHR.onreadystatechange = XHRChange;


    //建立加入房間訊息
    //送出訊息
    $.ajax({
        method: 'post',
        url: 'server/JoinRoom.php',
        cache: false,
        dataType: 'text',
        data: {
            'name': Config.UserName,
            'room': Config.RoomID,
        },
        success: function (data) {

            var ReturnData = JSON.parse(data);
            Config.UserGUID = ReturnData.guid; 

            console.log(ReturnData);

            $('.memberArea').empty();
            for (var i = 0; i < ReturnData.existmember.length; i++)
            {
                MemberJoin(
                    ReturnData.existmember[i].guid,
                    ReturnData.existmember[i].name,
                );
            }

            ThisChat.PollingStart();
        }
    });


    //離開房間
    this.LeaveRoom=function(){

        ChatXHR.abort();

        $.ajax({
            async: false,
            method: 'post',
            url: 'server/LeaveRoom.php',
            cache: false,
            dataType: 'text',
            data: {
                // 'guid': Config.UserGUID,
                'room': Config.RoomID,
            },
            success: function (data) {
                console.log(data);
            }
        });
    };

    this.PollingStart = function()
    {
        ChatXHR.abort();
        
        ChatXHR.open('GET', Config.ChatUrl + '?&user=' + Config.UserGUID + '&room=' + Config.RoomID +'&threshold='+XHRLastTimeThreshold, true);
        ChatXHR.send(null);
        console.log('PollingStart');
        console.log('?&user=' + Config.UserGUID + '&room=' + Config.RoomID + '&threshold=' + XHRLastTimeThreshold);
    }

    function XHRChange()
    {
        //state 500~600
        if (ChatXHR.status >= 500 && ChatXHR.status < 600)
        {
            console.log('ERROR ' + ChatXHR.status);

            $('.systemStateArea.re-connect').addClass('active');

            //重新輪巡
            ThisChat.PollingStart();
        }

        else if (ChatXHR.status == 200)
        {  
            $('.systemStateArea.re-connect').removeClass('active');

            if (ChatXHR.readyState == XMLHttpRequest.LOADING)
            {
                // console.log('response',ChatXHR.response);
                //console.log('response',ChatXHR.responseText);

                //去掉先前的訊息內容
                var newText = ChatXHR.responseText.replace(XHRLastText, '');
                XHRLastText = ChatXHR.responseText + '';
                // console.log(newText);

                if (newText == '') return;

                //解析物件
                var NewMessageData = JSON.parse(newText);
                if (NewMessageData.data.length == 0) return;
                // console.log(NewMessageData);


                //紀錄最後時間
                XHRLastTimeThreshold = NewMessageData.threshold;


                //計算延遲
                SystemDelay = new Date().getTime() - NewMessageData.threshold*1000;
                $('.funcArea .delay .inner').text(Math.floor(SystemDelay)+'ms');
                // console.log('delay', SystemDelay);


                //檢查卷軸是否至底
                var ScrollonBottom=false;
                // console.log($('.messageArea .inner').scrollTop() + $('.messageArea').height(), $('.messageArea .inner2').height())
                if ($('.messageArea .inner').scrollTop() + $('.messageArea').height() >= $('.messageArea .inner2').height())
                {
                    ScrollonBottom=true;
                }

                for (var i = 0; i < NewMessageData.data.length; i++) {

                    //檢查是否已經接收過
                    if (ReceiveGUID.indexOf(NewMessageData.data[i].guid) != -1)
                        continue;

                    //接收紀錄
                    ReceiveGUID.push(NewMessageData.data[i].guid);

                    //加入房間訊息
                    if (NewMessageData.data[i].system==1)
                    {
                        var MemberInfor = JSON.parse(NewMessageData.data[i].message);

                        MemberJoin(
                            MemberInfor.guid,
                            MemberInfor.name
                        );

                        //新增至畫面
                        var messageDiv = PutMessageOnChat(
                            Config.UserName,
                            MemberInfor.name+' 加入房間',
                            NewMessageData.data[i].time,
                            false,
                            1
                        );
                        $('.messageArea .inner2').append(messageDiv);
                        Notify(MemberInfor.name + ' 加入房間', 3000);
                        TitleNotify();
                    }

                    //離開訊息
                    else if (NewMessageData.data[i].system == 2)
                    {
                        var MemberInfor = JSON.parse(NewMessageData.data[i].message);

                        MemberLeave(
                            MemberInfor.guid
                        );

                        //新增至畫面
                        var messageDiv = PutMessageOnChat(
                            Config.UserName,
                            MemberInfor.name + ' 離開房間',
                            NewMessageData.data[i].time,
                            false,
                            1
                        );
                        $('.messageArea .inner2').append(messageDiv);
                        Notify(MemberInfor.name + ' 離開房間', 3000);
                        TitleNotify();
                    }

                    else if (NewMessageData.data[i].system == 3)
                    {
                        // console.log(NewMessageData.data[i]);
                        ChatXHR.abort();
                        // console.log('!!!!!!!!');
                        location.reload(); 
                    }

                    else
                    {
                        //新增至畫面
                        var messageDiv = PutMessageOnChat(
                            Config.UserName, 
                            Autolinker.link(NewMessageData.data[i].message,{
                                stripPrefix:false,
                            }), 
                            NewMessageData.data[i].time, 
                            false, 
                            0
                        );
                        $('.messageArea .inner2').append(messageDiv);
                        
    
                        //檢查是否是自己發送的
                        var FindGUID = SendGUID.indexOf(NewMessageData.data[i].guid);
                        if (FindGUID != -1) {
                            messageDiv.addClass('right');
                            $('.message-guid-' + SendGUID[FindGUID]).remove();
                        }
                        else
                        {
                            Notify();
                            TitleNotify();
                        }
                    }

                    
                }
                
                //移除過多訊息
                if ($('.messageArea .message').length > Config.SystemDisplayMessageMax) {
                    $('.messageArea .message:first').remove();
                }

                //自動捲動到最底
                if (ScrollonBottom)
                {
                    Scroll2Bottom();
                }

                // this can be also binary or your own content type 
                // (Blob and other stuff)
            }

            //當ChatXHR結束
            else if (ChatXHR.readyState == XMLHttpRequest.DONE) {
                //console.log('response',ChatXHR.response);
                console.log('DONE');

                //重新輪巡
                ThisChat.PollingStart();
            }
        }
    }

    //發送訊息
    this.SendMessage=function(Text) 
    {
        if (Text == '')
            return false;

        //製造一個假訊息
        var messageDiv = PutMessageOnChat(
            Config.UserName, 
            Text, 
            Math.floor(new Date().getTime() / 1000), 
            true, 
            0
        );
        messageDiv.addClass('right');

        //自動捲動到最底
        Scroll2Bottom();

        var GUID = MakeGUID();
        SendGUID.push(GUID);
        messageDiv.addClass('message-guid-' + GUID);

        ChatXHR.abort();

        //送出訊息
        $.ajax({
            method: 'post',
            url: 'server/send.php',
            cache: false,
            dataType: 'text',
            data: {
                'message': Text,
                'guid': GUID,
                'room': Config.RoomID,
            },
            success: function (data) {
                
            },
            complete: function (jqXHR, textStatus) {

                //重新輪巡
                // SendGUID=[];
                ReceiveGUID = [];
                ThisChat.PollingStart();
            }
        });

        SendMessageCallBack && SendMessageCallBack();
    }


    function MemberJoin(GUID,UserName)
    {
        if ($('.memberArea .member-guid-' + GUID).length>0)
        {
            return;
        }

        var memberDiv=$('<div></div>').addClass('member').addClass('member-guid-'+GUID);
        var imageDiv=$('<div></div>').addClass('image');
        var nameDiv=$('<div></div>').addClass('name').html(UserName);
        memberDiv.append(imageDiv,nameDiv);

        $('.memberArea').append(memberDiv);
    }

    function MemberLeave(GUID, UserName)
    {
        $('.memberArea .member-guid-' + GUID).remove();
    }


    //自動捲動到最底
    function Scroll2Bottom()
    {
        var height = $('.messageArea .inner2').height();
        $('.messageArea .inner').scrollTop(height);
    }

    //建立隨機識別碼
    function MakeGUID()
    {
        var ReturnStr = '';
        var Str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        for (var i = 0; i < 15; i++) {
            ReturnStr += Str[Math.floor(Math.random() * Str.length)];
        }

        return ReturnStr;
    }

    //將訊息新增至畫面上
    function PutMessageOnChat(name, message, time, loading, systemCode)
    {
        var messageDiv = $('<div></div>').addClass('message');

        if(systemCode!=0)
        {
            messageDiv.addClass('system');
        }

        var nameDiv = $('<div></div>').addClass('name');
        var textDiv = $('<div></div>').addClass('text');
        var textBodyDiv = $('<div></div>').addClass('body').html(message);
        textDiv.append(textBodyDiv);

        if (loading) {
            var loadingDiv = $('<div></div>').addClass('loading');
            textDiv.append(loadingDiv);
        }

        var timeStr = '';
        if (moment().format('YYYY-MM-DD') != moment.unix(time).format('YYYY-MM-DD')) {
            timeStr += moment.unix(time).format('MM-DD')+' ';
        }
        timeStr += moment.unix(time).format('HH:mm');

        var timeDiv = $('<div></div>').addClass('time').html(timeStr);
        messageDiv.append(nameDiv, textDiv, timeDiv);

        $('.messageArea .inner2').append(messageDiv);

        return messageDiv;
    }


}