# 075 Query Systems
1. schema.sql'i phpMyAdmin'de içe aktar. 2. config.php'yi düzenle (DB, TRC20 cüzdan adresi).
3. Owner oluştur: `php -r 'echo password_hash("SIFRE",PASSWORD_DEFAULT);'` -> users tablosuna role='owner' ekle.
4. Cron: `* * * * * php /yol/cron_tron.php` (USDT TRC20 ödemeleri otomatik onaylanır).
5. Admin > Kategoriler'den kendi tablonu/kolonlarını bağla, min rütbeyi seç.
Ödeme: her faturaya benzersiz ondalıklı tutar verilir, TronGrid'den eşleştirilip paket otomatik yüklenir.
Varsayım: fiyatlar USDT.
