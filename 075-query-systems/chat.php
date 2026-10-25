<?php require 'inc.php';$u=need();
if(isset($_GET['poll'])){header('Content-Type: application/json');echo json_encode(q('SELECT c.id,c.msg,c.created_at,u.username,u.role,u.avatar FROM chat c JOIN users u ON u.id=c.user_id ORDER BY c.id DESC LIMIT 50')->fetchAll());exit;}
if($_SERVER['REQUEST_METHOD']=='POST'){chk();$m=trim($_POST['msg']??'');if($m!==''){q('INSERT INTO chat(user_id,msg) VALUES(?,?)',[$u['id'],mb_substr($m,0,300)]);q('UPDATE users SET xp=xp+1 WHERE id=?',[$u['id']]);}exit('ok');}
head('Chat');?>
<div class=card><div id=box style="height:360px;overflow:auto"></div><input id=m maxlength=300 style="width:75%"><button onclick=send()>Gönder</button></div>
<script>
const e=s=>{const d=document.createElement('div');d.textContent=s;return d.innerHTML};
async function load(){const r=await (await fetch('chat.php?poll=1')).json();box.innerHTML=r.reverse().map(x=>'<p><b>'+e(x.username)+'</b> <small>['+e(x.role)+']</small>: '+e(x.msg)+'</p>').join('');box.scrollTop=box.scrollHeight}
async function send(){if(!m.value)return;const f=new FormData();f.append('msg',m.value);f.append('csrf','<?=csrf()?>');m.value='';await fetch('chat.php',{method:'POST',body:f});load()}
m.addEventListener('keydown',e=>{if(e.key=='Enter')send()});load();setInterval(load,3000);
</script>
