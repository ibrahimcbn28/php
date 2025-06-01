-- Yemek resimlerini güncelle
UPDATE foods SET image = 'iskender.jpg' WHERE name LIKE '%İskender%' OR name LIKE '%Iskender%';
UPDATE foods SET image = 'mercimek.jpeg' WHERE name LIKE '%Mercimek%';
UPDATE foods SET image = 'karnıyarık.jpeg' WHERE name LIKE '%Karnıyarık%' OR name LIKE '%Karniyarik%';
UPDATE foods SET image = 'künefe.jpeg' WHERE name LIKE '%Künefe%' OR name LIKE '%Kunefe%'; 