<?php require 'inc.php';$err='';
if($_SERVER['REQUEST_METHOD']=='POST'){chk();$n=trim($_POST['u']??'');$p=$_POST['p']??'';
 if(isset($_POST['reg'])){
  if(!preg_match('/^\w{3,32}$/',$n)||strlen($p)<6)$err='Geçersiz kullanıcı adı/şifre (min 6)';
  elseif(q('SELECT 1 FROM users WHERE username=?',[$n])->fetch())$err='Kullanıcı adı alınmış';
  elseif(q('SELECT COUNT(*) c FROM users WHERE reg_ip=?',[ip()])->fetch()['c']>=3)$err='Bu IP ile çok fazla kayıt';
  else{q('INSERT INTO users(username,pass,reg_ip) VALUES(?,?,?)',[$n,password_hash($p,PASSWORD_DEFAULT),ip()]);$err='Kayıt başarılı, giriş yapın.';}
 } else {
  $u=q('SELECT * FROM users WHERE username=?',[$n])->fetch();
  if(!$u||!password_verify($p,$u['pass']))$err='Hatalı giriş';
  elseif($u['banned'])$err='Hesap banlı';
  else{
   $ok=true;
   if(RANK[$u['role']]<2){ // cihaz limiti
    $tok=$_COOKIE['dev']??'';
    if(!preg_match('/^[a-f0-9]{64}$/',$tok))$tok=bin2hex(random_bytes(32));
    $known=q('SELECT 1 FROM devices WHERE user_id=? AND token=?',[$u['id'],$tok])->fetch();
    if(!$known){
     if(q('SELECT COUNT(*) c FROM devices WHERE user_id=?',[$u['id']])->fetch()['c']>=$u['device_limit']){$ok=false;$err='Cihaz limiti doldu. Admin ile iletişime geçin.';}
     else q('INSERT INTO devices(user_id,token,ip,ua) VALUES(?,?,?,?)',[$u['id'],$tok,ip(),substr($_SERVER['HTTP_USER_AGENT']??'',0,255)]);
    }
    if($ok)setcookie('dev',$tok,['expires'=>time()+3600*24*730,'httponly'=>true,'samesite'=>'Lax','path'=>'/']);
   }
   if($ok){session_regenerate_id(true);$_SESSION['uid']=$u['id'];q('UPDATE users SET last_ip=?,last_login=NOW() WHERE id=?',[ip(),$u['id']]);header('Location: panel.php');exit;}
  }
 }
}
head('Giriş');?>
<div class=card><h2>Giriş / Kayıt</h2><p class=err><?=h($err)?></p><form method=post><input type=hidden name=csrf value="<?=csrf()?>"><input name=u placeholder="Kullanıcı adı"><input name=p type=password placeholder=Şifre><button>Giriş</button><button name=reg value=1>Kayıt ol</button></form></div>
