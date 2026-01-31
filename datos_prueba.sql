-- Datos de prueba para la tabla `usuarios`
INSERT INTO `usuarios` 
(`nombre`, `apellido`, `dni`, `email`, `fecha_nacimiento`, `telefono`, `puesto`, `permisos`, `usuario`, `password`, `localidad`, `provincia`) 
VALUES 
('Juan', 'Perez', '11111111', 'juan.perez@example.com', '1990-05-15', '1122334455', 'Administrador', 'todo', 'admin_juan', 'pass123', 'Córdoba', 'Córdoba'),
('Maria', 'Garcia', '22222222', 'maria.garcia@example.com', '1992-08-20', '1122334466', 'Ventas', 'ver', 'maria_ventas', 'pass123', 'Rosario', 'Santa Fe'),
('Carlos', 'Lopez', '33333333', 'carlos.lopez@example.com', '1988-12-10', '1122334477', 'Operaciones', 'crear', 'carlos_ops', 'pass123', 'Mendoza', 'Mendoza');

-- Datos de prueba para la tabla `clientes`
INSERT INTO `clientes` 
(`apellido`, `nombre`, `dni`, `caracteristica_tel`, `numero_tel`, `email`, `usuario`, `direccion`, `fecha_nacimiento`, `password`, `activo`, `localidad`, `provincia`) 
VALUES 
('Martinez', 'Ana', '44444444', '011', '12345678', 'ana.martinez@example.com', 'ana_m', 'Calle Falsa 123', '1985-01-25', 'pass123', 1, 'CABA', 'Buenos Aires'),
('Sanchez', 'Roberto', '55555555', '351', '87654321', 'roberto.s@example.com', 'roberto_s', 'Av. Siempre Viva 742', '1975-03-30', 'pass123', 1, 'Córdoba', 'Córdoba'),
('Gomez', 'Elena', '66666666', '341', '11223344', 'elena.gomez@example.com', 'elena_g', 'San Martin 999', '1995-11-12', 'pass123', 1, 'Rosario', 'Santa Fe');
