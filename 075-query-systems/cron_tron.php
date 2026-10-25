<?php // Cron: */1 * * * * php /yol/cron_tron.php   veya web: cron_tron.php?key=SECRET
require 'inc.php';
if(PHP_SAPI!='cli'&&($_GET['key']??'')!==$C['cron_secret'])die('no');
q("UPDATE invoices SET status='expired' WHERE status='pending' AND created_at<DATE_SUB(NOW(),INTERVAL ? MINUTE)",[$C['invoice_ttl_min']]);
$url='https://api.trongrid.io/v1/accounts/'.$C['wallet'].'/transactions/trc20?only_to=true&limit=50&contract_address='.$C['usdt_contract'];
$ctx=stream_context_create(['http'=>['timeout'=>20,'header'=>$C['trongrid_key']?"TRON-PRO-API-KEY: {$C['trongrid_key']}\r\n":'']]);
$j=json_decode(@file_get_contents($url,false,$ctx),true);
foreach($j['data']??[] as $t){
 if(($t['to']??'')!==$C['wallet']||($t['token_info']['address']??'')!==$C['usdt_contract'])continue;
 $amt=number_format($t['value']/pow(10,(int)($t['token_info']['decimals']??6)),6,'.','');
 $inv=q("SELECT * FROM invoices WHERE status='pending' AND amount=? AND created_at<=FROM_UNIXTIME(?)+INTERVAL 1 MINUTE AND created_at>=FROM_UNIXTIME(?)-INTERVAL ? MINUTE",[$amt,intdiv($t['block_timestamp'],1000),intdiv($t['block_timestamp'],1000),$C['invoice_ttl_min']])->fetch();
 if(!$inv)continue;
 try{
  $pdo->beginTransaction();
  $r=q("UPDATE invoices SET status='paid',txid=? WHERE id=? AND status='pending'",[$t['transaction_id'],$inv['id']]);
  if($r->rowCount()){$pkg=q('SELECT * FROM packages WHERE id=?',[$inv['package_id']])->fetch();activate($inv['user_id'],$pkg);}
  $pdo->commit();echo "paid #{$inv['id']}\n";
 }catch(Throwable $e){$pdo->rollBack();}
}
