<?php require 'inc.php';$u=need();$m='';
if($_SERVER['REQUEST_METHOD']=='POST'){chk();
 if(!empty($_FILES['av']['tmp_name'])&&$_FILES['av']['size']<1048576){
  $info=@getimagesize($_FILES['av']['tmp_name']);$ext=[IMAGETYPE_JPEG=>'jpg',IMAGETYPE_PNG=>'png',IMAGETYPE_GIF=>'gif'][$info[2]??0]??null;
  if($ext){$f=$u['id'].'_'.bin2hex(random_bytes(4)).'.'.$ext;move_uploaded_file($_FILES['av']['tmp_name'],__DIR__.'/uploads/'.$f);q('UPDATE users SET avatar=? WHERE id=?',[$f,$u['id']]);$u['avatar']=$f;$m='Güncellendi';}else $m='Sadece jpg/png/gif';}
 if(strlen($_POST['np']??'')>=6){q('UPDATE users SET pass=? WHERE id=?',[password_hash($_POST['np'],PASSWORD_DEFAULT),$u['id']]);$m.=' Şifre değişti';}
}
head('Profil');?>
<div class=card><img class=av src="<?=$u['avatar']?'uploads/'.h($u['avatar']):'data:image/gif;base64,R0lGODlhAQABAAAAACw='?>"><h3><?=h($u['username'])?> - <?=h($u['role'])?></h3><p>Seviye <?=level($u['xp'])?> · <?=$u['xp']?> XP · Bakiye: <?=$u['balance']?> · Cihaz hakkı: <?=$u['device_limit']?></p><p class=ok><?=h($m)?></p>
<form method=post enctype=multipart/form-data><input type=hidden name=csrf value="<?=csrf()?>">Profil resmi: <input type=file name=av><br><input type=password name=np placeholder="Yeni şifre"><button>Kaydet</button></form></div>
