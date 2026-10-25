<?php require 'inc.php';$a=need('admin');$m='';
if($_SERVER['REQUEST_METHOD']=='POST'){chk();$id=(int)($_POST['id']??0);$t=q('SELECT * FROM users WHERE id=?',[$id])->fetch();$act=$_POST['act']??'';
 $isOwner=$a['role']=='owner';
 if($t&&RANK[$t['role']]>=RANK[$a['role']]&&$t['id']!=$a['id'])$m='Eşit/üst rütbeye işlem yapılamaz';
 elseif($act=='bal'&&$t){q('UPDATE users SET balance=balance+? WHERE id=?',[(float)$_POST['v'],$id]);$m='Bakiye güncellendi';}
 elseif($act=='pkg'&&$t){$p=q('SELECT * FROM packages WHERE id=?',[(int)$_POST['v']])->fetch();if($p){activate($id,$p);$m='Paket yüklendi';}}
 elseif($act=='dev'&&$t){q('UPDATE users SET device_limit=? WHERE id=?',[max(1,(int)$_POST['v']),$id]);$m='Cihaz limiti güncellendi';}
 elseif($act=='resetdev'&&$t){q('DELETE FROM devices WHERE user_id=?',[$id]);$m='Cihazlar sıfırlandı';}
 elseif($act=='ban'&&$t){q('UPDATE users SET banned=1-banned WHERE id=?',[$id]);$m='Ban değişti';}
 elseif($act=='role'&&$t&&$isOwner&&isset(RANK[$_POST['v']])&&$_POST['v']!='owner'){q('UPDATE users SET role=? WHERE id=?',[$_POST['v'],$id]);$m='Rol değişti';}
 elseif($act=='cat'&&$isOwner){q('INSERT INTO categories(name,table_name,columns_csv,min_role) VALUES(?,?,?,?)',[$_POST['n'],$_POST['tb'],$_POST['cols'],isset(RANK[$_POST['mr']])?$_POST['mr']:'vip']);$m='Kategori eklendi';}
 elseif($act=='delcat'&&$isOwner){q('DELETE FROM categories WHERE id=?',[(int)$_POST['id']]);}
}
head('Admin');$v=(int)($_GET['u']??0);
if($v){$t=q('SELECT * FROM users WHERE id=?',[$v])->fetch();?>
<div class=card><h3>Kullanıcı: <?=h($t['username'])?></h3><pre><?php unset($t['pass']);print_r(array_map('h',$t));?></pre>
<h4>Cihazlar</h4><table><?php foreach(q('SELECT * FROM devices WHERE user_id=?',[$v])->fetchAll() as $d)echo '<tr><td>'.h($d['ip']).'<td>'.h($d['ua']).'<td>'.h($d['created_at']).'</tr>';?></table>
<h4>Son sorgular</h4><table><?php foreach(q('SELECT * FROM query_log WHERE user_id=? ORDER BY id DESC LIMIT 50',[$v])->fetchAll() as $d)echo '<tr><td>'.h($d['term']).'<td>'.h($d['ip']).'<td>'.h($d['created_at']).'</tr>';?></table>
<h4>Faturalar</h4><table><?php foreach(q('SELECT * FROM invoices WHERE user_id=? ORDER BY id DESC',[$v])->fetchAll() as $d)echo '<tr><td>'.h($d['amount']).'<td>'.h($d['status']).'<td>'.h($d['txid']).'</tr>';?></table></div>
<?php }?>
<p class=ok><?=h($m)?></p>
<div class=card><h3>Kullanıcılar</h3><table><tr><th>ID<th>Kullanıcı<th>Rol<th>Bakiye<th>IP<th>Cihaz<th>İşlem</tr>
<?php foreach(q('SELECT * FROM users ORDER BY id DESC LIMIT 200')->fetchAll() as $r):?><tr><td><?=$r['id']?><td><a href="?u=<?=$r['id']?>"><?=h($r['username'])?></a><td><?=h($r['role'])?><?=$r['banned']?' (BAN)':''?><td><?=$r['balance']?><td><?=h($r['last_ip'])?><td><?=$r['device_limit']?>
<td><form method=post style=display:flex;flex-wrap:wrap><input type=hidden name=csrf value="<?=csrf()?>"><input type=hidden name=id value=<?=$r['id']?>><input name=v size=6 placeholder=değer><select name=act><option value=bal>Bakiye ekle<option value=pkg>Paket ID yükle<option value=dev>Cihaz limiti<option value=resetdev>Cihaz sıfırla<option value=ban>Ban/Unban<?php if($a['role']=='owner'):?><option value=role>Rol (free/vip/admin)<?php endif?></select><button>OK</button></form></tr><?php endforeach?></table><small>Paket ID: 1=Haftalık 2=Aylık 3=Yıllık 4=Sınırsız</small></div>
<?php if($a['role']=='owner'):?><div class=card><h3>Kategoriler (phpMyAdmin tablosu bağla)</h3><table><?php foreach(q('SELECT * FROM categories')->fetchAll() as $c):?><tr><td><?=h($c['name'])?><td><?=h($c['table_name'])?><td><?=h($c['columns_csv'])?><td><?=h($c['min_role'])?><td><form method=post><input type=hidden name=csrf value="<?=csrf()?>"><input type=hidden name=act value=delcat><input type=hidden name=id value=<?=$c['id']?>><button>Sil</button></form></tr><?php endforeach?></table>
<form method=post><input type=hidden name=csrf value="<?=csrf()?>"><input type=hidden name=act value=cat><input name=n placeholder=Ad><input name=tb placeholder=tablo><input name=cols placeholder="kolon1,kolon2"><select name=mr><option>free<option selected>vip<option>admin<option>owner</select><button>Ekle</button></form></div><?php endif?>
