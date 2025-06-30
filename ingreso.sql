-- Creación de la tabla `clientes`
CREATE TABLE `clientes` (
  `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `apellido` varchar(30) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `caracteristica_tel` varchar(5) NOT NULL,
  `numero_tel` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `activo` BOOLEAN DEFAULT 1,
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `localidad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni_unique` (`dni`),
  UNIQUE KEY `email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Creación de la tabla `usuarios` con todos los campos requeridos
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `puesto` varchar(50) DEFAULT NULL,
  `permisos` varchar(50) DEFAULT 'ninguno',
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `token` varchar(64) DEFAULT NULL,
  `localidad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `dni` (`dni`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar datos de prueba en la tabla `usuarios`

INSERT INTO `usuarios` 
(`nombre`, `apellido`, `dni`, `email`, `fecha_nacimiento`, `telefono`, `puesto`, `permisos`, `usuario`, `password`, `localidad`, `provincia`) 
VALUES 
('Damian', 'Durand', '52345678', 'durandamian523@gmail.com', '1985-03-10', '12345', 'Gerente', 'crear', 'gerente', '12345', 'Jujuy', 'Jujuy');

-- Creación de la tabla `turnos`
CREATE TABLE `turnos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100),
  `apellido` VARCHAR(100),
  `email` VARCHAR(100),
  `telefono` VARCHAR(20),
  `fecha` DATE,
  `hora` TIME,
  `comentario` TEXT,
  `presupuesto` BOOLEAN,
  `cliente_existente` BOOLEAN,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS horarios_disponibles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL,
    disponible BOOLEAN NOT NULL DEFAULT TRUE
);

INSERT INTO `horarios_disponibles` (`fecha_hora`, `disponible`) VALUES
('2024-08-01 09:00:00', TRUE),
('2024-08-02 11:00:00', TRUE),
('2024-08-03 13:00:00', TRUE),
('2024-08-04 15:00:00', TRUE),
('2024-08-05 10:00:00', TRUE);
-- Creación de la tabla `talentos`
CREATE TABLE `talentos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL,
  `apellido` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `puesto` VARCHAR(255) NOT NULL,
  `tel` VARCHAR(15) NOT NULL,
  `cv_path` VARCHAR(255) NOT NULL,
  `comentarios` TEXT, 
  `fecha_postulacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `proyectos` (
  `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_cliente` int(6) UNSIGNED NOT NULL,
  `nombre_proyecto` varchar(100) NOT NULL,
  `descripcion` text,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date,
  `estado` int(3) UNSIGNED NOT NULL DEFAULT 0, 
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_cliente`) REFERENCES `clientes`(`id`) ON DELETE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Creación de la tabla `archivos`
CREATE TABLE `archivos` (
  `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_proyecto` int(6) UNSIGNED NOT NULL,
  `nombre_archivo` varchar(100) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_proyecto`) REFERENCES proyectos(`id`) ON DELETE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- Creación de la tabla `presupuestos`
CREATE TABLE `presupuestos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `ocupacion` TEXT NOT NULL,
  `habitantes` TEXT NOT NULL,
  `seguridad` TEXT NOT NULL,
  `trabajo_en_casa` TEXT NOT NULL,
  `salud` TEXT NOT NULL,
  `telefono` VARCHAR(20) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `direccion` VARCHAR(255) NOT NULL,
  `fobias` TEXT,
  `intereses` TEXT,
  `rutinas` TEXT,
  `pasatiempos` TEXT,
  `visitas` VARCHAR(20), 
  `detalles_visitas` TEXT,
  `vehiculos` TEXT,
  `mascotas` TEXT,
  `aprendizaje` TEXT,
  `negocio` TEXT,
  `muebles` TEXT,
  `detalles_casa` TEXT,
  `turno` DATETIME, 
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `entrevista_completada` BOOLEAN DEFAULT FALSE -- Nuevo campo
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `primera_encuesta` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_presupuesto` INT NOT NULL,
  `tipo_cocina` ENUM('simple', 'con_isla', 'con_comedor', 'con_desayunador') NOT NULL,
  `tipo_bano` ENUM('simple', 'con_antebano') NOT NULL,
  `tipo_dormitorio_principal` ENUM('simple', 'con_bano', 'con_vestidor', 'con_bano_y_vestidor') NOT NULL,
  `tipo_dormitorio_secundario` ENUM('simple', 'con_bano', 'con_placard') NOT NULL,
  `tipo_comedor` ENUM('simple', 'integrado') NOT NULL,
  `tipo_estar` ENUM('simple', 'integrado', 'con_hogar') NOT NULL,
  `tipo_patio_servicio` ENUM('simple', 'con_lavadero', 'con_deposito') NOT NULL,
  `tipo_plantas` ENUM('ninguna', 'interior', 'exterior') NOT NULL,
  `tipo_escalera` ENUM('ninguna', 'interior', 'exterior') NOT NULL,
  `cantidad_habitantes` INT,
  `capacidad_quincho` INT,
  `otros_ambientes` TEXT,
  `tipo_cochera` ENUM('ninguna', 'simple', 'doble', 'galeria') NOT NULL,
  FOREIGN KEY (`id_presupuesto`) REFERENCES `presupuestos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `segunda_encuesta` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_presupuesto` INT NOT NULL,
    `tipo_cimiento` VARCHAR(50),
    `tipo_mamposteria` VARCHAR(50),
    `espesor_mamposteria` FLOAT,
    `tipo_estructura` VARCHAR(50),
    `tipo_techo` VARCHAR(50),
    `tipo_contrapiso` VARCHAR(50),
    `espesor_contrapiso` FLOAT,
    `observaciones_contrapiso` TEXT,
    FOREIGN KEY (`id_presupuesto`) REFERENCES `presupuestos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Creación de la tabla `configuraciones`
CREATE TABLE `configuraciones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `mostrar_talentos` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar datos en la tabla `configuraciones`
INSERT INTO `configuraciones` (`mostrar_talentos`) VALUES (1);

-- Creación de la tabla `servicios`
CREATE TABLE `servicios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(100) NOT NULL,
    `descripcion` TEXT NOT NULL,
    `imagen` VARCHAR(255) NOT NULL,
    `orden` INT NOT NULL DEFAULT 0,
    `activo` BOOLEAN DEFAULT TRUE,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar datos iniciales
INSERT INTO `servicios` (titulo, descripcion, imagen, orden) VALUES 
('CANCHAS', 'Canchas de todo tipo, de cesped sintetico o natural. Para uso familiar o de alquiler.', 'canchas.png', 1),
('HOGARES', 'Casas modernas, amplias, iluminadas, un hogar para vos y tu familia.', 'infantil002.png', 2),
('PISCINAS', 'Todo tipo de piscinas, piletas, a medida. Con diseño unico y sorprendente.', 'piscinas.png', 3);

-- Creación de la tabla `puestos_talento`
CREATE TABLE `puestos_talento` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `puesto` VARCHAR(255) NOT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ejemplo de inserción:
-- INSERT INTO `puestos_talento` (`puesto`, `cantidad`) VALUES ('Secretaria', 2);
