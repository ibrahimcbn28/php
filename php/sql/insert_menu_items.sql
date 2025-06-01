-- Önce kategorileri ekleyelim
INSERT INTO categories (name, description) VALUES
('Ana Yemekler', 'Geleneksel Türk mutfağından ana yemekler'),
('Çorbalar', 'Sıcacık ev yapımı çorbalar'),
('Tatlılar', 'Enfes tatlı çeşitleri');

-- Şimdi yemekleri ekleyelim
INSERT INTO foods (category_id, name, description, price, image) VALUES
-- Ana Yemekler
((SELECT id FROM categories WHERE name = 'Ana Yemekler'), 
'İskender Kebap', 
'Özel domates soslu, tereyağlı, yoğurtlu döner kebap',
85.00,
'iskender.jpg'),

((SELECT id FROM categories WHERE name = 'Ana Yemekler'),
'Karnıyarık',
'Patlıcan üzerinde kıymalı, közlenmiş biber ve domates ile',
65.00,
'karnıyarık.jpeg'),

-- Çorbalar
((SELECT id FROM categories WHERE name = 'Çorbalar'),
'Mercimek Çorbası',
'Geleneksel Türk mutfağının vazgeçilmezi, ev yapımı mercimek çorbası',
25.00,
'mercimek.jpeg'),

-- Tatlılar
((SELECT id FROM categories WHERE name = 'Tatlılar'),
'Künefe',
'Özel peyniri ve şerbeti ile hazırlanan geleneksel künefe',
55.00,
'künefe.jpeg'); 