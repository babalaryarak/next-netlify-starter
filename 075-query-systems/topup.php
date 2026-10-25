<?php require 'inc.php';$u=need();
if($_SERVER['REQUEST_METHOD']=='POST'){chk();$p=q('SELECT * FROM packages WHERE id=?',[(int)$_POST['pkg']])->fetch();
 if($p){q("UPDATE invoices SET status='expired' WHERE user_id=? AND status='pending'",[$u['id']]);
  // benzersiz 6 haneli ondalık: tutar ile ödeme eşleştirilir
  do{$amt=number_format($p['price']+random_int(1,9999)/1000000,6,'.','');}while(q("SELECT 1 FROM invoices WHERE amount=? AND status='pending'",[$amt])->fetch());
  q('INSERT INTO invoices(user_id,package_id,amount) VALUES(?,?,?)',[$u['id'],$p['id'],$amt]);}
}
head('Paketler');$inv=q("SELECT i.*,p.name FROM invoices i LEFT JOIN packages p ON p.id=i.package_id WHERE user_id=? ORDER BY i.id DESC LIMIT 5",[$u['id']])->fetchAll();?>
<div class=card><h3>Paketler (USDT TRC20)</h3><form method=post><input type=hidden name=csrf value="<?=csrf()?>"><?php foreach(q('SELECT * FROM packages')->fetchAll() as $p):?><button name=pkg value=<?=$p['id']?>><?=h($p['name'])?> - <?=$p['price']+0?> USDT</button><?php endforeach?></form></div>
<div class=card><h3>Faturalar</h3><table><tr><th>Paket<th>Gönderilecek tutar<th>Durum<th>Tarih</tr><?php foreach($inv as $i):?><tr><td><?=h($i['name'])?><td><b><?=h($i['amount'])?> USDT</b><td><?=h($i['status'])?><td><?=h($i['created_at'])?></tr><?php endforeach?></table>
<p>Adres (TRC20): <code><?=h($C['wallet'])?></code><br>Tam tutarı (ondalıklar dahil) gönderin; ödeme birkaç dakikada otomatik onaylanır ve üyelik yüklenir.</p></div>
