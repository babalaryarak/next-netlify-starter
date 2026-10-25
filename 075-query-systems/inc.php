<?php
session_start();
$C=require __DIR__.'/config.php';
$pdo=new PDO("mysql:host={$C['db']['host']};dbname={$C['db']['name']};charset=utf8mb4",$C['db']['user'],$C['db']['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
const RANK=['free'=>0,'vip'=>1,'admin'=>2,'owner'=>3];
function q($sql,$p=[]){global $pdo;$s=$pdo->prepare($sql);$s->execute($p);return $s;}
function h($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function ip(){return $_SERVER['REMOTE_ADDR']??'';}
function csrf(){return $_SESSION['csrf']??($_SESSION['csrf']=bin2hex(random_bytes(16)));}
function chk(){if(($_POST['csrf']??'')!==csrf())die('CSRF');}
function me(){
 if(empty($_SESSION['uid']))return null;
 $u=q('SELECT * FROM users WHERE id=?',[$_SESSION['uid']])->fetch();
 if(!$u||$u['banned']){session_destroy();return null;}
 // VIP süresi bitti mi?
 if($u['role']=='vip'&&!$u['unlimited']&&$u['vip_until']&&strtotime($u['vip_until'])<time()){q("UPDATE users SET role='free' WHERE id=?",[$u['id']]);$u['role']='free';}
 return $u;
}
function need($min='free'){$u=me();if(!$u){header('Location: index.php');exit;}if(RANK[$u['role']]<RANK[$min])die('Yetkisiz');return $u;}
function level($xp){return 1+intdiv($xp,100);}
function activate($uid,$pkg){
 $u=q('SELECT * FROM users WHERE id=?',[$uid])->fetch();
 if($u['role']=='admin'||$u['role']=='owner')return;
 if($pkg['days']==0){q("UPDATE users SET role='vip',unlimited=1,vip_until=NULL WHERE id=?",[$uid]);return;}
 $base=($u['role']=='vip'&&$u['vip_until']&&strtotime($u['vip_until'])>time())?strtotime($u['vip_until']):time();
 q("UPDATE users SET role='vip',vip_until=? WHERE id=?",[date('Y-m-d H:i:s',$base+$pkg['days']*86400),$uid]);
}
function head($t){$u=me();echo '<!doctype html><meta charset=utf-8><meta name=viewport content="width=device-width"><title>'.h($t).' - 075 Query Systems</title><link rel=stylesheet href=style.css><nav><b>075 Query Systems</b>';
 if($u){echo '<a href=panel.php>Sorgu</a><a href=topup.php>Paketler</a><a href=profile.php>Profil</a><a href=chat.php>Chat</a>';if(RANK[$u['role']]>=2)echo '<a href=admin.php>Admin</a>';echo '<a href=logout.php>Çıkış ('.h($u['username']).')</a>';}
 echo '</nav><main>';}
