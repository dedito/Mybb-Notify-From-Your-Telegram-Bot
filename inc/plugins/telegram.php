<?php
/* 
	** Notify From Your Telegram Bot.
	** Author: Pedram Asbaghi (Ponishweb.ir).
	** Special Thanks From Taha Shieenavaz.
*/

if(!defined('IN_MYBB'))
{
	die('This file cannot be accessed directly.');
}

class myTelegramBot {
	public static function sendTextMessage($msg,$token,$gpid){
		global $mybb;
		
		//Set Web Hook
		if(isset($gpid))
		{
			$replace = str_replace('http://','https://',$mybb->settings['bburl']);
				
			if(function_exists('curl_version')){
				curlMyT("https://api.telegram.org/bot$token/setwebhook?url=$replace");
			}
			else
			{
				file_get_contents("https://api.telegram.org/bot$token/setwebhook?url=$replace");
			}
			
			sleep('.1');
		}
		
		$context = stream_context_create(array(
		                'http' => array(
		                    'method' => 'POST',
		                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
		                    'timeout' => 10,
		                ),
		            ));
		$msg = urlencode($msg);
		
		//Send Messsage
		if(function_exists('curl_version')){
			$response =	curlMyT("https://api.telegram.org/bot$token/SendMessage?chat_id=$gpid&text=$msg&parse_mode=HTML");
		}
		else
		{
			$response =	file_get_contents("https://api.telegram.org/bot$token/SendMessage?chat_id=$gpid&text=$msg&parse_mode=HTML", false, $context);
		}
	}
	
	public static function prepareTextMessage($message,$arguments=array()){
		global $mybb;
		$final = "{BOARDNAME} \n\n ".$message;		
		$preDefined = array('{BOARDNAME}'=>$mybb->settings['bbname'],'{BOARDURL}'=>''.$mybb->settings['bburl']);
		//$preDefined = array('{BOARDNAME}'=>$mybb->settings['bbname'],'{BOARDURL}'=>$mybb->settings['bburl']);
		foreach ($preDefined as $key => $value)
			$final = str_replace($key, $value, $final);
		if(count($arguments) != 0){
			foreach ($arguments as $key => $value)
				$final = str_replace($key, $value, $final);
		}

		return $final;
	}
}

function telegram_info(){
	return array(
		"name"		=> "Notify From Your Telegram Bot",
		"description"		=> "Get notified of your latest forum events by your own telegram bot.",
		"author"		=> "Pedram Asbaghi [Ponishweb]",
		"version"		=> "1.0.1 mod.0.3a dedito",
		"codename" 			=> "telegram_bot",
		"compatibility"	=> "18*",
		'website'=>'https://github.com/dedito/Mybb-Notify-From-Your-Telegram-Bot', //'http://ponishweb.ir',
		'authorsite'=>'http://ponishweb.ir'
		);
}

function telegram_install(){
	global $db, $mybb;

	$setting_group = array(
	    'name' => 'my_telegram_settings',
	    'title' => 'Telegram Bot Settings',
	    'description' => 'Set Your Own Token in Here',
	    'disporder' => 6,
	    'isdefault' => 0
	);

	$gid = $db->insert_query("settinggroups", $setting_group);
	
	
	if(!empty($t = $mybb->settings['my_telegram_token']))
	{
		$token = '<a href="https://api.telegram.org/bot'.$t.'/getUpdates" target="_blank">HERE</a>';
	}
	else {
		$token = '
		<script>function alertMe() {alert("Please First Get Your Token And Save. Then recheck this page.");}</script>
		<a onclick="alertMe();">HERE</a>';
	}

	$setting_array = array(
	    'my_telegram_token' => array(
	        'title' => 'Your Bot Token',
	        'description' => 'Send a message to @BotFather and Make New bot and get your token.',
	        'optionscode' => 'text',
	        'disporder' => 1,
	        'value' => 0
	    ),
	    'my_telegram_gpid' => array(
	        'title' => 'Your Chat ID',
	        'description' => "For Get This ID, Click On $token (in the new window, Find Chat ID value and save in here).",
	        'optionscode' => 'text',
	        'disporder' => 2,
	        'value' => 0
	    ),
	    'my_telegram_login_status' => array(
	        'title' => 'Enbale Notifications on Login ?!',
	        'description' => 'If Enable Notifies You When Users Log into Forum',
	        'optionscode' => 'yesno',
	        'disporder' => 3,
	        'value' => 0
	    ),
	    'my_telegram_signup_status' => array(
	        'title' => 'Enbale Notifications on Signup ?!',
	        'description' => 'If Enable Notifies You When Users Signup on Forum',
	        'optionscode' => 'yesno',
	        'disporder' => 4,
	        'value' => 0
	    ),
	    'my_telegram_thread_status' => array(
	        'title' => 'Enbale Notifications on Thread Creation ?!',
	        'description' => 'If Enable Notifies You When Users Create Threads on Board',
	        'optionscode' => 'yesno',
	        'disporder' => 5,
	        'value' => 0
	    ),
	    'my_telegram_reply_status' => array(
	        'title' => 'Enbale Notifications on New Reply ?!',
	        'description' => 'If Enable Notifies You When Users Create New Reply on Board',
	        'optionscode' => 'yesno',
	        'disporder' => 6,
	        'value' => 0
	    ),
	    'my_telegram_security_status' => array(
	        'title' => 'Enbale Security Notifications ?!',
	        'description' => 'When Somebody Wants to access Admin Panel / Mod Cpanel Notifies You !',
	        'optionscode' => 'yesno',
	        'disporder' => 7,
	        'value' => 0
	    )
		);

		foreach($setting_array as $name => $setting)
		{
		    $setting['name'] = $name;
		    $setting['gid'] = $gid;

		    $db->insert_query('settings', $setting);
		}
		
		rebuild_settings();
	
}
function telegram_is_installed()
{
	global $mybb;
	if(!empty($mybb->settings['my_telegram_token']) && !empty($mybb->settings['my_telegram_gpid']))
	{
	    return true;
	}
	return false;
}

function telegram_uninstall()
{
	global $db;
	$db->delete_query('settings', "name IN ('my_telegram_token','my_telegram_gpid','telegram_signup_status','telegram_login_status','telegram_thread_status','telegram_security_status')");
	$db->delete_query('settinggroups', "name = 'my_telegram_settings'");
	rebuild_settings();
}

function my_telegram_activate(){}
function my_telegram_deactivate(){}

function my_login_notifications($obj){
	global $mybb;
	if(!$mybb->settings['my_telegram_login_status']){return FALSE;}
	$login_message = 'Użytkownik " {NAME} " zalogował się na Forum'."\n\n{BOARDURL}"; // 'User " {NAME} " Has Been Logged into Forum'."\n\n{BOARDURL}";
	$data = get_object_vars($obj);
	myTelegramBot::sendTextMessage( myTelegramBot::prepareTextMessage($login_message,array('{NAME}'=>$data['login_data']['username'])) ,$mybb->settings['my_telegram_token'],$mybb->settings['my_telegram_gpid']);	
}

function my_thread_notifications(){
	global $db,$mybb;
	if(!$mybb->settings['my_telegram_thread_status']){return FALSE;}
	//$thread_message = "Użytkownik {UNAME} rozpoczął nowy wątek ' {TOPICNAME} '\n {BOARDURL}{TOPICURL}"; //"A Topic Called ' {TOPICNAME} ' Has Been Started By {UNAME}\n {TOPICURL}";
	$thread_message = 'Użytkownik {UNAME} rozpoczął nowy wątek <a href="{BOARDURL}{TOPICURL}">{TOPICNAME}</a>';
	$ThreadQuery = $db->query("SELECT subject,username,tid FROM ".TABLE_PREFIX."threads ORDER BY tid DESC LIMIT 1");
	$LastThread = $db->fetch_array($ThreadQuery);
	//myTelegramBot::sendTextMessage(myTelegramBot::prepareTextMessage($thread_message,array('{TOPICNAME}'=>$LastThread['subject'],'{UNAME}'=>$LastThread['username'],'{TOPICURL}'=> '{BOARDURL}/showthread.php?tid='.$LastThread['tid'])),$mybb->settings['my_telegram_token'],$mybb->settings['my_telegram_gpid']);
	myTelegramBot::sendTextMessage(myTelegramBot::prepareTextMessage($thread_message,array('{TOPICNAME}'=>$LastThread['subject'],'{UNAME}'=>$LastThread['username'],'{TOPICURL}'=>'/showthread.php?tid='.$LastThread['tid'])),$mybb->settings['my_telegram_token'],$mybb->settings['my_telegram_gpid']);
}

function my_reply_notifications(){
	global $db,$mybb;
	if(!$mybb->settings['my_telegram_reply_status']){return FALSE;}
	$row = $db->fetch_array($db->simple_select("posts", "pid, tid, fid, username, subject", "", ['order_by' => 'dateline DESC', 'limit' => 1]));
	$link = '<a href="'.$mybb->settings['bburl'].'/'.get_post_link($row['pid'], $row['tid']).'#pid'.$row['pid'].'">'.$row['subject'].'</a>';
	$reply_message = 'Użytkownik {UNAME} napisał nową odpowiedź w wątku {TOPICURL}'; 
	$ThreadQuery = $db->query("SELECT subject,username,tid FROM ".TABLE_PREFIX."threads ORDER BY tid DESC LIMIT 1");
	$LastThread = $db->fetch_array($ThreadQuery);
	$PostQuery = $db->query("SELECT subject,username,pid FROM ".TABLE_PREFIX."posts ORDER BY pid DESC LIMIT 1");
	$LastPost = $db->fetch_array($PostQuery);
	//myTelegramBot::sendTextMessage(myTelegramBot::prepareTextMessage($thread_message,array('{TOPICNAME}'=>$LastThread['subject'],'{UNAME}'=>$LastThread['username'],'{TOPICURL}'=> '{BOARDURL}/showthread.php?tid='.$LastThread['tid'])),$mybb->settings['my_telegram_token'],$mybb->settings['my_telegram_gpid']);
	//myTelegramBot::sendTextMessage(myTelegramBot::prepareTextMessage($reply_message,array('{TOPICNAME}'=>$LastPost['subject'],'{UNAME}'=>$LastPost['username'],'{TOPICURL}'=> '/showthread.php?tid='.$LastPost['tid']/*'&pid='.$LastPost['pid']'#pid'.$LastPost['pid']*/)),$mybb->settings['my_telegram_token'],$mybb->settings['my_telegram_gpid']);
	myTelegramBot::sendTextMessage(myTelegramBot::prepareTextMessage($reply_message,array('{TOPICNAME}'=>$LastPost['subject'],'{UNAME}'=>$LastPost['username'],'{TOPICURL}'=>$link)),$mybb->settings['my_telegram_token'],$mybb->settings['my_telegram_gpid']);
}

function my_signup_notifications(){
	global $db,$mybb;
	if(!$mybb->settings['my_telegram_signup_status']){return FALSE;}
	$Signup_Message = "Zarajestrował się nowy użytkownik {UNAME} \n {BOARDURL}"; //"{UNAME} Has Been Successfuly Registred. \n {BOARDURL}";
	$LastUserQuery = $db->query('SELECT username FROM '.TABLE_PREFIX.'users ORDER BY uid DESC LIMIT 1');
	$LastUser = $db->fetch_array($LastUserQuery);
	myTelegramBot::sendTextMessage(myTelegramBot::prepareTextMessage($Signup_Message,array('{UNAME}'=>$LastUser['username'])),$mybb->settings['my_telegram_token'],$mybb->settings['my_telegram_gpid']);
}
function my_adminpanel_notifications(){
	global $mybb;
	if(!$mybb->settings['my_telegram_security_status']){return FALSE;}
	if(!$_COOKIE['AdminpanelReached']){
		$AdminPanel_Message = " Zalogowano do ACP z adresu IP ({IP})\n\n{BOARDURL}"; //" I have Detected A Successful Login To Admin Panel From ({IP})\n\n{BOARDURL}";
		setcookie('AdminpanelReached', 1, time()+3600);
		myTelegramBot::sendTextMessage(myTelegramBot::prepareTextMessage($AdminPanel_Message,array('{IP}'=>$_SERVER['REMOTE_ADDR'])),$mybb->settings['my_telegram_token'],$mybb->settings['my_telegram_gpid']);
	}
}
function my_modcp_notifications(){
	global $mybb;
	if(!$mybb->settings['telegram_security_status']){return FALSE;}
	if(!$_COOKIE['ModcpReached']){
		$Modcp_Message = "Zalogowano do MCP z adresu IP ({IP})\n\n{BOARDURL}"; //"I have Detected A Successful Login To Modcp From ({IP})\n\n{BOARDURL}";
		setcookie('ModcpReached', 1, time()+3600);
		myTelegramBot::sendTextMessage(myTelegramBot::prepareTextMessage($Modcp_Message,array('{IP}'=>$_SERVER['REMOTE_ADDR'])),$mybb->settings['my_telegram_token'],$mybb->settings['my_telegram_gpid']);
	}
}

function my_token_warning(){
	global $mybb;
	
	if(empty($mybb->settings['my_telegram_token']))
	{
		echo '
			<div style="background: #FBE3E4;border: 1px solid #A5161A;color: #A5161A;text-align: center;padding: 5px 20px;margin-bottom: 15px;font-size: 11px;word-wrap: break-word;">First, make a new bot and get your own token.Then send one message to your bot through your account. then visit Plugin Setting Page.
		 </div>
		';
	    return false;
	}
	return true;
	
}

function curlMyT($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_ENCODING, '');
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0');
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
}

$plugins->add_hook('datahandler_login_complete_end', 'my_login_notifications');
$plugins->add_hook('newthread_do_newthread_end','my_thread_notifications');
$plugins->add_hook('newreply_do_newreply_end','my_reply_notifications');
$plugins->add_hook('member_do_register_end','my_signup_notifications');
$plugins->add_hook('admin_load','my_token_warning');
$plugins->add_hook('admin_load','my_adminpanel_notifications');
$plugins->add_hook('modcp_end','my_modcp_notifications');
