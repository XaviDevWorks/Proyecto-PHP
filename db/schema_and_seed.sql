-- Esquema
CREATE TABLE IF NOT EXISTS attractions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  maintenance TINYINT(1) NOT NULL DEFAULT 0,
  duration_minutes INT DEFAULT NULL,
  min_height_cm INT DEFAULT NULL,
  category VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ticket_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) UNIQUE NOT NULL,
  label VARCHAR(100) NOT NULL,
  price DECIMAL(8,2) NOT NULL,
  description VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  buyer_email VARCHAR(150) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  ticket_type_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(8,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Semilla mínima (ajústala a tu gusto)
INSERT INTO ticket_types (code,label,price,description) VALUES
('ADULT','Adulto',30.00,'Entrada general adulto'),
('CHILD','Niño (4-12)',10.00,'Entrada reducida para niños'),
('SENIOR','Senior',15.00,'Entrada para mayores de 65')
ON DUPLICATE KEY UPDATE price=VALUES(price), label=VALUES(label), description=VALUES(description);

INSERT INTO attractions
  (name, description, maintenance, duration_minutes, min_height_cm, category)
VALUES
  ('Lanzadera Orbital','Aceleración vertical que simula un despegue orbital.',0,3,130,'Espacio'),
  ('Paseo por la Galaxia','Recorrido familiar con constelaciones y nebulosas en 360º.',0,8,NULL,'Espacio'),
  ('Base Lunar Alfa','Dark ride por una colonia lunar con animatrónicos y efectos de baja gravedad.',0,6,NULL,'Espacio'),
  ('Hipervelocidad','Montaña rusa indoor con lanzamiento a hipervelocidad y túneles estelares.',0,2,140,'Espacio'),
  ('Comando Asteroide','Shooter interactivo: defiende la estación de una lluvia de asteroides.',0,7,120,'Simulador'),
  ('Cúpula Planetaria','Cine 4D en domo con viaje guiado por el sistema solar.',0,12,NULL,'Cine 4D'),
  ('Anillo de Saturno','Columpio gigante con vista panorámica del parque y anillos luminosos.',0,4,125,'Adrenalina'),
  ('Academia de Cadetes','Zona infantil con mini-entrenamientos de astronauta.',0,10,NULL,'Infantil'),
  ('Paseo de Meteoritos','Flume acuático con meteoritos y una caída final entre vapor criogénico.',1,5,120,'Acuático'),
  ('Centro de Control Misión Z','Escape-room tecnológico por equipos: evita el fallo del reactor.',0,30,NULL,'Aventura'),
  ('EVA: Caminata Espacial','Simulador con arnés que emula una caminata extravehicular.',1,6,130,'Simulador'),
  ('Observatorio de Exoplanetas','Miradores interactivos y espectroscopios para detectar mundos lejanos.',0,9,NULL,'Educativa');
