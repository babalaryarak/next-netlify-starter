// 075 Query Systems - Node önizleme (PHP kodunun görünüm/akış taklidi, bellek içi veri)
const http=require('http'),fs=require('fs'),path=require('path');
const RANK={free:0,vip:1,admin:2,owner:3};
let user={username:'demo',role:'free',xp:35,balance:0,device_limit:1,vip_until:null};
const pk=[[1,'Haftalık',400],[2,'Aylık',750],[3,'Yıllık',1400],[4,'Sınırsız',3500]];
const cats=[{id:1,name:'Demo Ücretsiz',min:'free'},{id:2,name:'Demo VIP',min:'vip'}];
const rows=[{ad:'Ali',soyad:'Yılmaz',telefon:'5550000001',sehir:'İstanbul'},{ad:'Ayşe',soyad:'Demir',telefon:'5550000002',sehir:'Ankara'}];
const chat=[{u:'owner',r:'owner',m:'Hoş geldiniz!'}];let inv=[];
const users=[{id:1,username:'owner',role:'owner',balance:0,ip:'1.2.3.4',dev:1},{id:2,username:'demo',role:'free',balance:0,ip:'5.6.7.8',dev:1}];
const e=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
function page(t,body){const a=RANK[user.role]>=2?'<a href=/admin>Admin</a>':'';
return `<!doctype html><meta charset=utf-8><meta name=viewport content="width=device-width"><title>${t} - 075 Query Systems</title><link rel=stylesheet href=/style.css><nav><b>075 Query Systems</b><a href=/>Sorgu</a><a href=/topup>Paketler</a><a href=/profile>Profil</a><a href=/chat>Chat</a>${a}<a href=/login>Çıkış</a><span style="margin-left:auto">Önizleme rolü: <a href="/role?r=free">free</a> <a href="/role?r=vip">vip</a> <a href="/role?r=admin">admin</a> <a href="/role?r=owner">owner</a></span></nav><main>${body}</main>`}
const lvl=x=>1+Math.floor(x/100);
function panel(q){let msg='',res='';
if(q){const c=cats.find(x=>x.id==q.cat);if(RANK[user.role]<RANK[c.min])msg=c.min+' gerekli';else{const t=(q.t||'').toLowerCase();const r=rows.filter(o=>Object.values(o).some(v=>v.toLowerCase().includes(t)));user.xp+=5;
res=`<div class=card><b>${r.length} sonuç</b><table><tr>${Object.keys(rows[0]).map(k=>'<th>'+k).join('')}</tr>${r.map(o=>'<tr>'+Object.values(o).map(v=>'<td>'+e(v)).join('')+'</tr>').join('')}</table></div>`}}
return page('Sorgu',`<div class=card><h3>Rütbe: ${user.role.toUpperCase()} | Seviye ${lvl(user.xp)} (${user.xp} XP)</h3><form><select name=cat>${cats.map(c=>`<option value=${c.id} ${RANK[user.role]>=RANK[c.min]?'':'disabled'}>${c.name} [${c.min}]`).join('')}</select><input name=t placeholder=Aranan required><button>Sorgula</button></form><p class=err>${msg}</p></div>${res}<div class=card><h3>Haftalık En Çok Sorgu Yapanlar</h3><table><tr><th>#<th>Kullanıcı<th>Sorgu</tr><tr><td>1<td>owner<td>42</tr><tr><td>2<td>${user.username}<td>${Math.floor(user.xp/5)}</tr></table></div>`)}
http.createServer((req,res)=>{const u=new URL(req.url,'http://x');const q=Object.fromEntries(u.searchParams);let out;
if(u.pathname=='/style.css'){res.setHeader('Content-Type','text/css');return res.end(fs.readFileSync(path.join(__dirname,'..','style.css')))}
if(u.pathname=='/role'){user.role=q.r in RANK?q.r:'free';res.writeHead(302,{Location:'/'});return res.end()}
if(u.pathname=='/login')out=page('Giriş',`<div class=card><h2>Giriş / Kayıt</h2><form action=/><input placeholder="Kullanıcı adı"><input type=password placeholder=Şifre><button>Giriş</button><button type=button>Kayıt ol</button></form></div>`);
else if(u.pathname=='/topup'){if(q.pkg){const p=pk.find(x=>x[0]==q.pkg);inv.unshift({n:p[1],a:(p[2]+Math.random()/100).toFixed(6)})}
out=page('Paketler',`<div class=card><h3>Paketler (USDT TRC20)</h3><form>${pk.map(p=>`<button name=pkg value=${p[0]}>${p[1]} - ${p[2]} USDT</button>`).join('')}</form></div><div class=card><h3>Faturalar</h3><table><tr><th>Paket<th>Gönderilecek tutar<th>Durum</tr>${inv.map(i=>`<tr><td>${i.n}<td><b>${i.a} USDT</b><td>pending</tr>`).join('')}</table><p>Adres (TRC20): <code>TXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX</code></p></div>`)}
else if(u.pathname=='/profile')out=page('Profil',`<div class=card><img class=av src="data:image/gif;base64,R0lGODlhAQABAAAAACw=" style=background:#30363d><h3>${user.username} - ${user.role}</h3><p>Seviye ${lvl(user.xp)} · ${user.xp} XP · Bakiye: ${user.balance} · Cihaz hakkı: ${user.device_limit}</p><input type=file><br><input type=password placeholder="Yeni şifre"><button>Kaydet</button></div>`);
else if(u.pathname=='/chat'){if(q.msg){chat.push({u:user.username,r:user.role,m:q.msg});user.xp++}
out=page('Chat',`<div class=card><div style="height:360px;overflow:auto">${chat.map(c=>`<p><b>${e(c.u)}</b> <small>[${c.r}]</small>: ${e(c.m)}</p>`).join('')}</div><form><input name=msg maxlength=300 style=width:75% required><button>Gönder</button></form></div>`)}
else if(u.pathname=='/admin'){if(RANK[user.role]<2)out=page('Admin','<p class=err>Yetkisiz</p>');else out=page('Admin',`<div class=card><h3>Kullanıcılar</h3><table><tr><th>ID<th>Kullanıcı<th>Rol<th>Bakiye<th>IP<th>Cihaz<th>İşlem</tr>${users.map(r=>`<tr><td>${r.id}<td>${r.username}<td>${r.role}<td>${r.balance}<td>${r.ip}<td>${r.dev}<td><select><option>Bakiye ekle<option>Paket yükle<option>Cihaz limiti<option>Ban</select><button>OK</button></tr>`).join('')}</table></div>`)}
else out=panel(q.t?q:null);
res.setHeader('Content-Type','text/html;charset=utf-8');res.end(out)}).listen(8075,()=>console.log('http://localhost:8075'));
