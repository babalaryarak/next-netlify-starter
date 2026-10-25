<?php require 'inc.php';$u=need();head('Sorgu');$res=null;$msg='';
$cats=q('SELECT * FROM categories ORDER BY id')->fetchAll();
if($_SERVER['REQUEST_METHOD']=='POST'){chk();
 $cat=q('SELECT * FROM categories WHERE id=?',[(int)$_POST['cat']])->fetch();$t=trim($_POST['t']??'');
 if(!$cat||$t==='')$msg='Geçersiz sorgu';
 elseif(RANK[$u['role']]<RANK[$cat['min_role']])$msg='Bu kategori için '.$cat['min_role'].' gerekli';
 else{
  $used=q('SELECT COUNT(*) c FROM query_log WHERE user_id=? AND created_at>=CURDATE()',[$u['id']])->fetch()['c'];
  if($u['role']=='free'&&$used>=$C['free_daily_limit'])$msg='Günlük ücretsiz limit doldu. VIP alın.';
  else{
   $tbl=preg_replace('/[^\w]/','',$cat['table_name']);
   $cols=array_filter(array_map(fn($c)=>preg_replace('/[^\w]/','',$c),explode(',',$cat['columns_csv'])));
   $where=implode(' OR ',array_map(fn($c)=>"`$c` LIKE ?",$cols));
   $p=array_fill(0,count($cols),'%'.addcslashes($t,'%_\\').'%');
   $res=q("SELECT ".implode(',',array_map(fn($c)=>"`$c`",$cols))." FROM `$tbl` WHERE $where LIMIT 50",$p)->fetchAll();
   q('INSERT INTO query_log(user_id,category_id,term,ip) VALUES(?,?,?,?)',[$u['id'],$cat['id'],substr($t,0,255),ip()]);
   q('UPDATE users SET xp=xp+5 WHERE id=?',[$u['id']]);
  }
 }
}
?>
<div class=card><h3>Rütbe: <?=h(strtoupper($u['role']))?> | Seviye <?=level($u['xp'])?> (<?=$u['xp']?> XP)<?php if($u['role']=='vip'): ?> | <?=$u['unlimited']?'Sınırsız':'Bitiş: '.h($u['vip_until'])?><?php endif ?></h3>
<form method=post><input type=hidden name=csrf value="<?=csrf()?>"><select name=cat><?php foreach($cats as $c):$ok=RANK[$u['role']]>=RANK[$c['min_role']];?><option value=<?=$c['id']?> <?=$ok?'':'disabled'?>><?=h($c['name'])?> [<?=h($c['min_role'])?>]</option><?php endforeach ?></select><input name=t placeholder=Aranan required><button>Sorgula</button></form><p class=err><?=h($msg)?></p></div>
<?php if($res!==null): ?><div class=card><b><?=count($res)?> sonuç</b><table><?php foreach($res as $i=>$r){if(!$i)echo '<tr>'.implode('',array_map(fn($k)=>'<th>'.h($k).'</th>',array_keys($r))).'</tr>';echo '<tr>'.implode('',array_map(fn($v)=>'<td>'.h($v).'</td>',$r)).'</tr>';}?></table></div><?php endif ?>
<div class=card><h3>Haftalık En Çok Sorgu Yapanlar</h3><table><tr><th>#<th>Kullanıcı<th>Sorgu</tr>
<?php foreach(q('SELECT u.username,COUNT(*) c FROM query_log l JOIN users u ON u.id=l.user_id WHERE l.created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY) GROUP BY u.id ORDER BY c DESC LIMIT 10')->fetchAll() as $i=>$r)echo '<tr><td>'.($i+1).'<td>'.h($r['username']).'<td>'.$r['c'].'</tr>';?></table></div>
