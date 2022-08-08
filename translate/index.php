<?php
        define('API_KEY', "5076568599:AAFdPuFU6hHNONK3rdh83NIafQMnM3Sqs-4");
        function bot($method, $datas=[]){
            $url = "https://api.telegram.org/bot".API_KEY."/".$method;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);

            $res = curl_exec($ch);

            if (curl_error($ch)) {
                var_dump(curl_error($ch));
            }else{
                return json_decode($res);
            }
        }
        ///////////////
        function del($nomi){
            array_map('unlink', glob("step/$nomi.*"));
        }
        
        function ACL($callbackQueryId, $text = null, $showAlert = false)
            {
                return bot('answerCallbackQuery', [
                    'callback_query_id' => $callbackQueryId,
                    'text' => $text,
                    'show_alert' => $showAlert,
                ]);
            }


        ////////////
        function html($tx){
            return str_replace(['<','>'],['&#60;','&#62;'],$tx);
        }
        function translate($source,$target,$text) {
            $url = "https://translate.google.com/translate_a/single?client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dj=1&hl=es-ES&ie=UTF-8&oe=UTF-8&inputm=2&otf=2&iid=1dd3b944-fa62-4b55-b330-74909a99969e";
            $fields = array(
                'sl'=>urlencode($source),
                'tl'=>urlencode($target),
                'q'=>urlencode($text)
            );
            $fields_string = "";
            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone TRANSLATE_OPM5_TEST_1');
            $result = curl_exec($ch);
            curl_close($ch);
            return json_decode($result);
        }
        include 'db.php';

        $update = json_decode(file_get_contents('php://input'));
        $message = $update->message;
        $chat_id = $message->chat->id;
        $type = $message->chat->type;
        $miid =$message->message_id;
        $name = $message->from->first_name;
        $lname = $message->from->last_name;
        $full_name = $name . " " . $lname;
        $full_name = realstring(html($full_name));
        $user = $message->from->username;
        $fromid = $message->from->id;
        $text = $message->text;
        $title = $message->chat->title;
        $chatuser = $message->chat->username;
        $chatuser = $chatuser ? $chatuser : "Shaxsiy Guruh!";
        $caption = $message->caption;
        $text_link = $entities->type;
        $left_chat_member = $message->left_chat_member;
        $new_chat_member = $message->new_chat_member;
        //call
        $ida = $callback->id;
        $cbid = $callback->from->id;

        //editmessage
        $callback = $update->callback_query;
        $qid = $callback->id;
        $mes = $callback->message;
        $mid = $mes->message_id;
        $cmtx = $mes->text;
        $cid = $callback->message->chat->id;
        $ctype = $callback->message->chat->type;
        $cbid = $callback->from->id;
        $cbuser = $callback->from->username;
        $data = $callback->data;

        // include 'checkuser.php';
        // include 'addchannel.php';

        function slt($fromid) {
            global $conn;
            $slt = "SELECT id FROM tableOfWh WHERE fromid='{$fromid}'";
            $query = mysqli_query($conn,$slt);
            if (mysqli_num_rows($query)>0) {
                return true;
            }else{
                return false;
            }
        }
        function ins($fromid,$type="private") {
            global $conn, $full_name, $user;
            $ins = "INSERT INTO tableOfWh (fromid,name,user,account,del,menu) VALUES ('{$fromid}','{$full_name}','{$user}','{$type}','0','')";
            $query = mysqli_query($conn,$ins);
        }
        if ($message) {
            if ($type == "private") {
                if ($text == "/start") {
                    if (slt($fromid) == false) {
                        ins($fromid,"private");
                    }else{
                        $upd = "UPDATE tableOfWh SET del = '0' WHERE fromid='{$fromid}'";
                        $query = mysqli_query($conn,$upd);
                    }
                    bot('sendMessage',[
                        'chat_id'=>$fromid,
                        'text'=>"<b>Salom 👋</b><i>" . $full_name . "</i>,\n<i>Botga xush kelibsiz siz bu bot orqali 6 ta tilga o'zaro tarjima qilishingiz mumkin 😊!</i>",
                        'parse_mode'=>'html',
                        'reply_markup'=>json_encode([
                            'inline_keyboard'=>[
                                [['text'=>"Tilni tanlash va tarjimani boshlash", 'callback_data'=>"start"],],
                            ],
                        ]),
                    ]);
                }else if($text){
                    $slt = "SELECT * FROM tableOfWh WHERE fromid='{$fromid}'";
                    $query = mysqli_query($conn,$slt);
                    $row = mysqli_fetch_assoc($query);
                    $menu = $row["menu"];
                    $exp = explode("-", $menu);
                    $trans = translate($exp[0],$exp[1],$text);
                    $matn = "";
                    foreach ($trans->sentences as $key => $value) {
                        $matn .= $value->trans;
                    }
                    $matn = str_replace('\n', "\n", $matn);
                    bot('sendMessage',[
                        'chat_id'=>$fromid,
                        'text'=>$matn,
                        'reply_markup'=>json_encode([
                            'inline_keyboard'=>[
                                [['text'=>"🔄 Tilni almashtirish", 'callback_data'=>"back"],],
                            ],
                        ]),
                    ]);
                }
                //admin
                if ($fromid == $admin) {
                    $step = file_get_contents("step/admin.step");
                    $menu = file_get_contents("step/menu.txt");
                    if ($text == "/admin") {
                        $slt = "SELECT * FROM tableOfWh";
                        $query = mysqli_query($conn,$slt);
                        $allUsers = mysqli_num_rows($query);

                        $groups_slt = "SELECT * FROM tableOfWh WHERE account='private'";
                        $groups_query = mysqli_query($conn,$groups_slt);
                        $users = mysqli_num_rows($groups_query);
                        $groups = $allUsers - $users;

                        $users_slt = "SELECT * FROM tableOfWh WHERE del='1' and account='private'";
                        $users_query = mysqli_query($conn,$users_slt);
                        $noactive_users = mysqli_num_rows($users_query);

                        $groups_slt = "SELECT * FROM tableOfWh WHERE del='1' and account='group'";
                        $groups_query = mysqli_query($conn,$groups_slt);
                        $noactive_groups = mysqli_num_rows($groups_query);

                        $noactive_groups = $noactive_groups ? $noactive_groups : '0';
                        $noactive_users = $noactive_users ? $noactive_users : '0';

                        $matn = "📊 Bot statistikasi:\n\n" . "👥 Barcha azolar: " . $allUsers . " ta\n👥 Guruhlar: " . $groups . "\n👤 Userlar: " . $users . "\n\nNofaollar:\n❌ Nofaol userlar: " . $noactive_users . "\n❌ Nofaol guruhlar: " . $noactive_groups;
                            bot('sendMessage',[
                                'chat_id'=>$fromid,
                                'text'=>"Admin panelga kirdingiz",
                                'reply_markup'=>json_encode([
                                    'resize_keyboard'=>true,
                                    'keyboard'=>[
                                        [['text'=>"📩Xabar yuborish"],['text'=>"📊 Bot statistikasi"],],
                                    ],
                                ]),
                            ]);
                    }
                    
                    if ($text == "📊 Bot statistikasi") {
                        $slt = "SELECT * FROM tableOfWh";
                        $query = mysqli_query($conn,$slt);
                        $allUsers = mysqli_num_rows($query);

                        $groups_slt = "SELECT * FROM tableOfWh WHERE account='private'";
                        $groups_query = mysqli_query($conn,$groups_slt);
                        $users = mysqli_num_rows($groups_query);
                        $groups = $allUsers - $users;

                        $users_slt = "SELECT * FROM tableOfWh WHERE del='1' and account='private'";
                        $users_query = mysqli_query($conn,$users_slt);
                        $noactive_users = mysqli_num_rows($users_query);

                        $groups_slt = "SELECT * FROM tableOfWh WHERE del='1' and account='group'";
                        $groups_query = mysqli_query($conn,$groups_slt);
                        $noactive_groups = mysqli_num_rows($groups_query);

                        $noactive_groups = $noactive_groups ? $noactive_groups : '0';
                        $noactive_users = $noactive_users ? $noactive_users : '0';

                        $matn = "📊 Bot statistikasi:\n\n" . "👥 Barcha azolar: " . $allUsers . " ta\n👥 Guruhlar: " . $groups . "\n👤 Userlar: " . $users . "\n\nNofaollar:\n❌ Nofaol userlar: " . $noactive_users . "\n❌ Nofaol guruhlar: " . $noactive_groups;
                        bot('sendMessage',[
                            'chat_id'=>$fromid,
                            'text'=>$matn,
                            'parse_mode' => 'markdown',
                        ]);
                    }
                    if ($text == "📩Xabar yuborish") {
                        bot('sendMessage',[
                            'chat_id'=>$fromid,
                            'text'=>"💌Xabaringizni yuboring!"
                        ]);
                        if (!file_exists("step")) mkdir("step");
                        file_put_contents("step/admin.step", "0");
                        file_put_contents("step/menu.txt", "senAds");
                    }
                
                    

                    if ($step == "0" && $menu == "senAds") {
                        if ($message) {
                            bot('copyMessage',[
                                'chat_id'=>$fromid,
                                'from_chat_id'=>$fromid,
                                'message_id'=>$miid,
                            ]);
                            bot('sendMessage',[
                                'chat_id'=>$fromid,
                                'text'=>"Yuborishlikka tayyormi❔",
                                'reply_markup'=>json_encode([
                                    'resize_keyboard'=>true,
                                    'keyboard'=>[
                                        [['text'=>"✅Tayyor"],['text'=>"❌Bekor qilish"],],
                                    ],
                                ]),
                            ]);
                            file_put_contents("step/admin.step","1");
                            file_put_contents("step/admin.for_id",$fromid);
                            file_put_contents("step/admin.for_mid",$miid);
                        }
                    }
                    
                    if($text == "❌Bekor qilish" or $data == "clear"){
                        ACL($ida);
                        del($cbid);
                        del($chat_id);
                        if(isset($text)) $url = "$chat_id";
                        if(isset($data)) $url = "$cbid";
                            bot('sendMessage', [
                            'chat_id'=>$url,
                            'text'=>"Bekor qilindi",
                            'reply_markup'=>json_encode([
                                    'resize_keyboard'=>true,
                                    'keyboard'=>[
                                        [['text'=>"📩Xabar yuborish"],['text'=>"📊 Bot statistikasi"],],
                                    ],
                                ]),
                            ]);
                        }
                    if ($step == "1" && $menu == "senAds") {
                        if ($text == "✅Tayyor") {
                            bot('sendMessage',[
                                'chat_id'=>$fromid,
                                'text'=>"⏳Xabaringiz yuborilmoqda...",
                                'reply_markup'=>json_encode([
                                    'resize_keyboard'=>true,
                                    'keyboard'=>[
                                        [['text'=>"📩Xabar yuborish"],['text'=>"📊 Bot statistikasi"],],
                                    ],
                                ]),
                                
                            ]);
                            file_put_contents("step/admin.step","2");
                        }
                    }
                }
            }else if($type == "supergroup" || $type == "group"){
                if ($text == "/start") {
                    if (slt($chat_id) == false) {
                        ins($chat_id,"group");
                    }
                    bot('sendMessage',[
                        'chat_id'=>$chat_id,
                        'text'=>"<b>Salom 👋</b><i>" . $full_name . "</i>,\n<i>Botga xush kelibsiz siz bu bot orqali 6 ta tilga o'zaro tarjima qilishingiz mumkin 😊!</i>",
                        'parse_mode'=>'html',
                        'reply_markup'=>json_encode([
                            'inline_keyboard'=>[
                                [['text'=>"Tarjimani boshlash", 'url'=>"@test_uzb77_bot"],],
                            ],
                        ]),
                    ]);
                }
            }
        }
        if ($callback) {
            if ($data == "start") {
                bot('editMessageText',[
                    'chat_id'=>$cbid,
                    'message_id'=>$mid,
                    'text'=>"<b>Tilni tanlang✅:</b>",
                    'parse_mode'=>'html',
                    'reply_markup'=>json_encode([
                        'inline_keyboard'=>[
                            [['text'=>"🏴󠁧󠁢󠁥󠁮󠁧󠁿 en-uz 🇺🇿", 'callback_data'=>"en-uz"],['text'=>"🇺🇿 uz-en 🏴󠁧󠁢󠁥󠁮󠁧󠁿", 'callback_data'=>"uz-en"],],
                            [['text'=>"🇷🇺 ru-uz 🇺🇿", 'callback_data'=>"ru-uz"],['text'=>"🇺🇿 uz-ru 🇷🇺",'callback_data'=>"ru-uz"],],
                            [['text'=>"🏴󠁧󠁢󠁥󠁮󠁧󠁿 en-ru 🇷🇺",'callback_data'=>"en-ru"],['text'=>"🇷🇺 ru-en 🏴󠁧󠁢󠁥󠁮󠁧󠁿",'callback_data'=>"ru-en"],],
                        ],
                    ]),
                ]);
            }
            if ($data == "back") {
                $upd = "UPDATE tableOfWh SET menu='' WHERE fromid='{$cbid}'";
                $query = mysqli_query($conn,$upd);
                bot('editMessageText',[
                    'chat_id'=>$cbid,
                    'message_id'=>$mid,
                    'text'=>"<b>Tilni tanlang✅:</b>",
                    'parse_mode'=>'html',
                    'reply_markup'=>json_encode([
                        'inline_keyboard'=>[
                            [['text'=>"🏴󠁧󠁢󠁥󠁮󠁧󠁿 en-uz 🇺🇿", 'callback_data'=>"en-uz"],['text'=>"🇺🇿 uz-en 🏴󠁧󠁢󠁥󠁮󠁧󠁿", 'callback_data'=>"uz-en"],],
                            [['text'=>"🇷🇺 ru-uz 🇺🇿", 'callback_data'=>"ru-uz"],['text'=>"🇺🇿 uz-ru 🇷🇺",'callback_data'=>"ru-uz"],],
                            [['text'=>"🏴󠁧󠁢󠁥󠁮󠁧󠁿 en-ru 🇷🇺",'callback_data'=>"en-ru"],['text'=>"🇷🇺 ru-en 🏴󠁧󠁢󠁥󠁮󠁧󠁿",'callback_data'=>"ru-en"],],
                        ]
                    ]),
                ]);
            }else if($data !== "start"){
                $upd = "UPDATE tableOfWh SET menu='{$data}' WHERE fromid='{$cbid}'";
                $query = mysqli_query($conn,$upd);
                bot('editMessageText',[
                    'chat_id'=>$cbid,
                    'message_id'=>$mid,
                    'text'=>"*Tarjima uchun matn yuboring!*",
                    'parse_mode'=>"markdown",
                    'reply_markup'=>json_encode([
                        'inline_keyboard'=>[
                            [['text'=>"🔙 Ortga", 'callback_data'=>'goback'],],
                        ],
                    ]),
                ]);
            }
        
        }
        if ($_GET['send']) {
            $get = file_get_contents("step/admin.step");
            if ($get == "2") {
                $default = file_get_contents("step/admin.default");
                $after = $default + '60';

                $slt = "SELECT * FROM tableOfWh WHERE id>='{$default}' AND id<='{$after}'";
                $query = mysqli_query($conn,$slt);
                if (mysqli_num_rows($query)>0) {
                    $from_id = file_get_contents("step/admin.for_id");
                    $for_mid = file_get_contents("step/admin.for_mid");
                    foreach ($query as $key => $value) {
                        bot('copyMessage',[
                            'chat_id'=>$value["fromid"],
                            'from_chat_id'=>$from_id,
                            'message_id'=>$for_mid
                        ]);
                    }
                    file_put_contents("step/admin.default", $after);
                }else{
                    bot('sendMessage',[
                        'chat_id'=>$admin,
                        'text'=>"✅Xabaringiz muvaffaqiyatli yuborildi!",
                        'reply_markup'=>json_encode([
                            'resize_keyboard'=>true,
                            'keyboard'=>[
                                [['text'=>"📩Xabar yuborish"],['text'=>"📊 Bot statistikasi"],],
                            ],
                        ]),
                    ]);
                    array_map( 'unlink', array_filter((array) glob("step/*")));
                }
            }
        }
        //wheather
        if ($_GET['weather']) {
            $weatherUrl = file_get_contents("https://api.openweathermap.org/data/2.5/weather?q=Uzbekistan&appid=351ccba9bd91ef24823bed7cf66380b8");
            $getUrl = json_decode($weatherUrl);
        
            $country = $getUrl->name;
            $gs = $getUrl->wind->gust;
            $sp = $getUrl->wind->speed;
            $ob = $getUrl->main->temp_max - "273.15";

            $default = 0;
            $after = $default + 60;

            $slt = "SELECT * FROM tableOfWh WHERE id>='{$default}' AND id<='{$after}'";
            $query = mysqli_query($conn,$slt);
            if (mysqli_num_rows($query)>0) {
                foreach ($query as $key => $value) {
                        bot('sendMessage',[
                            'chat_id'=>$value["fromid"],
                            'text'=>"Mamlakat:".$country."\nobhavo:".$ob."°C"."\nshamol tezligi: ".$sp,
                            'parse_mode'=>"html",
                        ]);
                }
                $default+=$after;
            }else {
                bot('sendMessage',[
                    'chat_id'=>$admin,
                    'text'=>"xabar yuborildi ",
                    'parse_mode'=>"html",
                ]);
            }
        }

        //
        if (($update->my_chat_member and $update->my_chat_member->new_chat_member->status == "kicked") || ($update->my_chat_member->new_chat_member->status == "left")) {
            $id = $update->my_chat_member->chat->id;
            $slt = "UPDATE tableOfWh SET del = '1' WHERE fromid = '{$id}'";
            $query = mysqli_query($conn,$slt);
        }
    ?>