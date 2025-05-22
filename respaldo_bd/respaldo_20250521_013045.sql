-- Respaldo generado el 2025-05-21 01:30:45

DROP TABLE IF EXISTS `avisos`;
CREATE TABLE `avisos` (
  `ID_AVISO` int(11) NOT NULL AUTO_INCREMENT,
  `CONTENIDO` text NOT NULL,
  `FECHA` datetime NOT NULL DEFAULT current_timestamp(),
  `RFC_AUTOR` varchar(13) DEFAULT NULL,
  `ID_PERIODO` int(11) DEFAULT NULL,
  `ES_AUTOMATICO` tinyint(1) DEFAULT 0,
  `DESTINATARIOS` set('Administrador','Jefe Vinculación','Jefe Departamento','Egresado') NOT NULL DEFAULT 'Administrador,Jefe Vinculación,Jefe Departamento,Egresado',
  `FECHA_PROGRAMADA` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`ID_AVISO`),
  KEY `RFC_AUTOR` (`RFC_AUTOR`),
  KEY `ID_PERIODO` (`ID_PERIODO`),
  CONSTRAINT `avisos_ibfk_1` FOREIGN KEY (`RFC_AUTOR`) REFERENCES `usuario` (`RFC`),
  CONSTRAINT `avisos_ibfk_2` FOREIGN KEY (`ID_PERIODO`) REFERENCES `periodo_encuesta` (`ID_PERIODO`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `avisos` VALUES('4','¡Nuevo periodo de encuesta abierto: Encuesta de Seguimiento de Egresados ENE/JUN 2025! Por favor, completa tu encuesta lo antes posible.','2025-05-18 11:14:52',NULL,'2','1','Egresado','2025-05-18 19:14:52');
INSERT INTO `avisos` VALUES('5','Recuerda generar los reportes de seguimiento de los egresados del periodo presente','2025-05-18 20:31:00','GOTS040611BH5',NULL,'0','Administrador,Jefe Vinculación,Jefe Departamento','2025-05-18 20:31:00');


DROP TABLE IF EXISTS `cuestionario_respuesta`;
CREATE TABLE `cuestionario_respuesta` (
  `ID_CUESTIONARIO` int(11) NOT NULL AUTO_INCREMENT,
  `CURP` varchar(18) DEFAULT NULL,
  `TIPO` enum('GENERAL','QUIMICA') DEFAULT NULL,
  `FECHA_APLICACION` datetime DEFAULT current_timestamp(),
  `COMPLETO` tinyint(1) DEFAULT 0,
  `ID_EMPRESA` int(11) DEFAULT NULL,
  `ID_PERIODO` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID_CUESTIONARIO`),
  KEY `CURP` (`CURP`),
  KEY `FK_CUESTIONARIO_EMPRESA` (`ID_EMPRESA`),
  KEY `ID_PERIODO` (`ID_PERIODO`),
  CONSTRAINT `FK_CUESTIONARIO_EMPRESA` FOREIGN KEY (`ID_EMPRESA`) REFERENCES `empresa` (`ID_EMPRESA`),
  CONSTRAINT `ID_PERIODO` FOREIGN KEY (`ID_PERIODO`) REFERENCES `periodo_encuesta` (`ID_PERIODO`),
  CONSTRAINT `cuestionario_respuesta_ibfk_1` FOREIGN KEY (`CURP`) REFERENCES `egresado` (`CURP`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cuestionario_respuesta` VALUES('1','GACV041201MVZRRLA4','GENERAL','2025-05-18 22:46:30','1','1','2');
INSERT INTO `cuestionario_respuesta` VALUES('2','PAM8000101MVRITV0',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('3','PAM8000101MVRITV1',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('4','PAM8000101MVRITV2',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('5','PAM8000101MVRITV3',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('6','PAM8000101MVRITV4',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('7','PAM8000101MVRITV5',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('8','PAM8000101MVRITV6',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('9','PAM8000101MVRITV7',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('10','PAM8000101MVRITV8',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('11','PAM8000101MVRITV9',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('12','PAM8000101MVRITV10',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('13','PAM8000101MVRITV11',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('14','PAM8000101MVRITV12',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('15','PAM8000101MVRITV13',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('16','PAM8000101MVRITV14',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('17','PAM8000101MVRITV15',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('18','PAM8000101MVRITV16',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('19','PAM8000101MVRITV17',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('20','PAM8000101MVRITV18',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('21','PAM8000101MVRITV19',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('22','PAM8000101MVRITV20',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('23','PAM8000101MVRITV21',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('24','PAM8000101MVRITV22',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('25','PAM8000101MVRITV23',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('26','PAM8000101MVRITV24',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('27','PAM8000101MVRITV25',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('28','PAM8000101MVRITV26',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('29','PAM8000101MVRITV27',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('30','PAM8000101MVRITV28',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('31','PAM8000101MVRITV29',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('32','PAM8000101MVRITV30',NULL,'2025-05-20 14:20:45','0',NULL,'1');
INSERT INTO `cuestionario_respuesta` VALUES('33','PAM8000101MVRITV0',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('34','PAM8000101MVRITV1',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('35','PAM8000101MVRITV2',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('36','PAM8000101MVRITV3',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('37','PAM8000101MVRITV4',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('38','PAM8000101MVRITV5',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('39','PAM8000101MVRITV6',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('40','PAM8000101MVRITV7',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('41','PAM8000101MVRITV8',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('42','PAM8000101MVRITV9',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('43','PAM8000101MVRITV10',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('44','PAM8000101MVRITV11',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('45','PAM8000101MVRITV12',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('46','PAM8000101MVRITV13',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('47','PAM8000101MVRITV14',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('48','PAM8000101MVRITV15',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('49','PAM8000101MVRITV16',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('50','PAM8000101MVRITV17',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('51','PAM8000101MVRITV18',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('52','PAM8000101MVRITV19',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('53','PAM8000101MVRITV20',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('54','PAM8000101MVRITV21',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('55','PAM8000101MVRITV22',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('56','PAM8000101MVRITV23',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('57','PAM8000101MVRITV24',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('58','PAM8000101MVRITV25',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('59','PAM8000101MVRITV26',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('60','PAM8000101MVRITV27',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('61','PAM8000101MVRITV28',NULL,'2025-05-20 14:21:00','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('62','PAM8000101MVRITV29',NULL,'2025-05-20 14:21:01','0',NULL,'2');
INSERT INTO `cuestionario_respuesta` VALUES('63','PAM8000101MVRITV30',NULL,'2025-05-20 14:21:01','0',NULL,'2');


DROP TABLE IF EXISTS `egresado`;
CREATE TABLE `egresado` (
  `CURP` varchar(18) NOT NULL,
  `NUM_CONTROL` varchar(20) DEFAULT NULL,
  `NOMBRE` varchar(100) DEFAULT NULL,
  `APELLIDO_PATERNO` varchar(50) DEFAULT NULL,
  `APELLIDO_MATERNO` varchar(50) DEFAULT NULL,
  `FECHA_NACIMIENTO` date DEFAULT NULL,
  `SEXO` enum('Hombre','Mujer') DEFAULT NULL,
  `ESTADO_CIVIL` enum('Soltero(a)','Casado(a)','Otro','') DEFAULT NULL,
  `CALLE` varchar(100) DEFAULT NULL,
  `COLONIA` varchar(100) DEFAULT NULL,
  `CODIGO_POSTAL` varchar(10) DEFAULT NULL,
  `CIUDAD` varchar(100) DEFAULT NULL,
  `MUNICIPIO` varchar(100) DEFAULT NULL,
  `ESTADO` varchar(100) DEFAULT NULL,
  `EMAIL` varchar(100) DEFAULT NULL,
  `CONTRASENA` varchar(100) NOT NULL,
  `TELEFONO` varchar(20) DEFAULT NULL,
  `CARRERA` enum('Licenciatura en Administración','Ingeniería Bioquímica','Ingeniería Eléctrica','Ingeniería Electrónica','Ingeniería Industrial','Ingeniería Mecatrónica','Ingeniería Mecánica','Ingeniería en Sistemas Computacionales','Ingeniería Química','Ingeniería en Energías Renovables','Ingeniería en Gestión Empresarial') DEFAULT NULL,
  `FECHA_EGRESO` date DEFAULT NULL,
  `TITULADO` tinyint(1) DEFAULT NULL,
  `VERIFICADO` tinyint(1) DEFAULT 0,
  `CODIGO_VERIFICACION` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`CURP`),
  UNIQUE KEY `NUM_CONTROL` (`NUM_CONTROL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `egresado` VALUES('GACV041201MVZRRLA4','22020772','VALERIA','GARCIA','CORONA','2001-12-01','Mujer','Soltero(a)','GERANIOS LOTE 6','DOS CAMINOS','91726','VERACRUZ','VERACRUZ','VERACRUZ','ceciliacorona01veracruz@gmail.com','$2y$10$dCGsF9akGMeAkoRp.B0ky.EFTRZYYvbaI9IPXOojpPqEC4vJO3Ly6','2299294556','Ingeniería en Sistemas Computacionales','2024-12-16','1','1',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV0','20022000','0','P0','M0','2001-12-26','Mujer','Soltero(a)','GERANIOS LOTE 6','DOS CAMINOS','94290','Boca del Rio','Veracruz','Veracruz','20022000@veracruz.tecnm.mx','','2299294556','Ingeniería en Sistemas Computacionales','2025-12-19','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV1','20022001','1','P1','M1','2001-12-27','Mujer','Soltero(a)','','','94290','Boca del Rio','Veracruz','Veracruz','20022001@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2025-12-20','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV10','20022010','10','P10','M10','2002-01-05','Hombre','Casado(a)','','','76090','Queretaro','Queretaro','Queretaro','20022010@veracruz.tecnm.mx','','','Ingeniería en Gestión Empresarial','2020-12-29','1','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV11','20022011','11','P11','M11','2002-01-06','Hombre','Casado(a)','','','76090','Queretaro','Queretaro','Queretaro','20022011@veracruz.tecnm.mx','','','Ingeniería en Gestión Empresarial','2020-12-30','1','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV12','20022012','12','P12','M12','2002-01-07','Hombre','Casado(a)','','','76090','Queretaro','Queretaro','Queretaro','20022012@veracruz.tecnm.mx','','','Ingeniería en Gestión Empresarial','2020-12-31','1','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV13','20022013','13','P13','M13','2002-01-08','Hombre','Casado(a)','','','76090','Queretaro','Queretaro','Queretaro','200220013@veracruz.tecnm.mx','','','Ingeniería en Gestión Empresarial','2021-01-01','1','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV14','20022014','14','P14','M14','2002-01-09','Hombre','Casado(a)','','','76090','Queretaro','Queretaro','Queretaro','200220014@veracruz.tecnm.mx','','','Ingeniería en Gestión Empresarial','2021-01-02','1','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV15','20022015','15','P15','M15','2002-01-10','Hombre','Casado(a)','','','76090','Queretaro','Queretaro','Queretaro','20022015@veracruz.tecnm.mx','','','Ingeniería en Gestión Empresarial','2021-01-03','1','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV16','20022016','16','P16','M16','2002-01-11','Hombre','Casado(a)','','','76090','Queretaro','Queretaro','Queretaro','20022016@veracruz.tecnm.mx','','','Ingeniería en Gestión Empresarial','2021-01-04','1','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV17','20022017','17','P17','M17','2002-01-12','Hombre','Casado(a)','','','76090','Queretaro','Queretaro','Queretaro','20022017@veracruz.tecnm.mx','','','Ingeniería en Gestión Empresarial','2021-01-05','1','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV18','20022018','18','P18','M18','2002-01-13','Hombre','Casado(a)','','','76090','Queretaro','Queretaro','Queretaro','20022018@veracruz.tecnm.mx','','','Ingeniería en Gestión Empresarial','2021-01-06','1','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV19','20022019','19','P19','M19','2002-01-14','Hombre','Casado(a)','','','76090','Queretaro','Queretaro','Queretaro','20022019@veracruz.tecnm.mx','','','Ingeniería en Gestión Empresarial','2021-01-07','1','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV2','20022002','2','P2','M2','2001-12-28','Mujer','Soltero(a)','','','94290','Boca del Rio','Veracruz','Veracruz','200220002veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2025-12-21','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV20','20022020','20','P20','M20','2002-01-15','Hombre','Casado(a)','','','76090','Queretaro','Queretaro','Queretaro','20022020@veracruz.tecnm.mx','','','Ingeniería en Gestión Empresarial','2021-01-08','1','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV21','20022021','21','P21','M21','2002-01-16','Mujer','Otro','','','91895','Veracruz','Veracruz','Veracruz','20022021@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2015-01-09','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV22','20022022','22','P22','M22','2002-01-17','Mujer','Otro','','','91896','Veracruz','Veracruz','Veracruz','20022022@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2015-01-10','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV23','20022023','23','P23','M23','2002-01-18','Mujer','Otro','','','91897','Veracruz','Veracruz','Veracruz','20022023@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2015-01-11','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV24','20022024','24','P24','M24','2002-01-19','Mujer','Otro','','','91898','Veracruz','Veracruz','Veracruz','200220024@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2015-01-12','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV25','20022025','25','P25','M25','2002-01-20','Mujer','Otro','','','91899','Veracruz','Veracruz','Veracruz','20022025@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2015-01-13','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV26','20022026','26','P26','M26','2002-01-21','Mujer','Otro','','','91900','Veracruz','Veracruz','Veracruz','20022026@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2015-01-14','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV27','20022027','27','P27','M27','2002-01-22','Mujer','Otro','','','91901','Veracruz','Veracruz','Veracruz','20022027@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2015-01-15','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV28','20022028','28','P28','M28','2002-01-23','Mujer','Otro','','','91902','Veracruz','Veracruz','Veracruz','20022028@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2015-01-16','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV29','20022029','29','P29','M29','2002-01-24','Mujer','Otro','','','91903','Veracruz','Veracruz','Veracruz','20022029@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2015-01-17','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV3','20022003','3','P3','M3','2001-12-29','Mujer','Soltero(a)','','','94290','Boca del Rio','Veracruz','Veracruz','20022003@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2025-12-22','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV30','20022030','30','P30','M30','2002-01-25','Mujer','Otro','','','91904','Veracruz','Veracruz','Veracruz','20022030@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2015-01-18','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV4','20022004','4','P4','M4','2001-12-30','Mujer','Soltero(a)','','','94290','Boca del Rio','Veracruz','Veracruz','20022004@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2025-12-23','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV5','20022005','5','P5','M5','2001-12-31','Mujer','Soltero(a)','','','94290','Boca del Rio','Veracruz','Veracruz','20022005@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2025-12-24','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV6','20022006','6','P6','M6','2002-01-01','Mujer','Soltero(a)','','','94290','Boca del Rio','Veracruz','Veracruz','20022006@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2025-12-25','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV7','20022007','7','P7','M7','2002-01-02','Mujer','Soltero(a)','','','94290','Boca del Rio','Veracruz','Veracruz','20022007@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2025-12-26','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV8','20022008','8','P8','M8','2002-01-03','Mujer','Soltero(a)','','','94290','Boca del Rio','Veracruz','Veracruz','20022008@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2025-12-27','0','0',NULL);
INSERT INTO `egresado` VALUES('PAM8000101MVRITV9','20022009','9','P9','M9','2002-01-04','Mujer','Soltero(a)','','','94290','Boca del Rio','Veracruz','Veracruz','20022009@veracruz.tecnm.mx','','','Ingeniería en Sistemas Computacionales','2025-12-28','0','0',NULL);


DROP TABLE IF EXISTS `empresa`;
CREATE TABLE `empresa` (
  `ID_EMPRESA` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO_ORGANISMO` enum('Público','Privado','Social') DEFAULT NULL,
  `GIRO` text DEFAULT NULL,
  `RAZON_SOCIAL` varchar(150) DEFAULT NULL,
  `CALLE` varchar(100) DEFAULT NULL,
  `NUMERO` varchar(10) DEFAULT NULL,
  `COLONIA` varchar(100) DEFAULT NULL,
  `CODIGO_POSTAL` varchar(10) DEFAULT NULL,
  `CIUDAD` varchar(100) DEFAULT NULL,
  `MUNICIPIO` varchar(100) DEFAULT NULL,
  `ESTADO` varchar(100) DEFAULT NULL,
  `TELEFONO` varchar(20) DEFAULT NULL,
  `EMAIL` varchar(100) DEFAULT NULL,
  `PAGINA_WEB` varchar(150) DEFAULT NULL,
  `JEFE_INMEDIATO_NOMBRE` varchar(100) DEFAULT NULL,
  `JEFE_INMEDIATO_PUESTO` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID_EMPRESA`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `empresa` VALUES('1','Privado','Desarrollo de hardware','Software y Tecnología S.A. de C.V.','Calle Paseo','#302','Col. El Trébol','91726','Alvarado','Alvarado','Veracruz','(01-229) 654 3211','logistica@grupologisticoveracruz.com','https://grupologisticoveracruz.com','Andrés Jiménez','Coordinador de Operaciones');


DROP TABLE IF EXISTS `historial_consulta`;
CREATE TABLE `historial_consulta` (
  `ID_CONSULTA` int(11) NOT NULL AUTO_INCREMENT,
  `RFC` varchar(13) NOT NULL,
  `TIPO_INFORME` enum('Informe Estadístico','Reporte Detallado por Egresado','Estado de Participación','Informe por Sección','Comparativo Histórico') NOT NULL,
  `FILTROS` text DEFAULT NULL,
  `RUTA_ARCHIVO` varchar(255) DEFAULT NULL,
  `FECHA` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`ID_CONSULTA`),
  KEY `RFC` (`RFC`),
  CONSTRAINT `historial_consulta_ibfk_1` FOREIGN KEY (`RFC`) REFERENCES `usuario` (`RFC`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `historial_consulta` VALUES('1','GOTS040611BH5','Reporte Detallado por Egresado','{\"carrera\":\"\",\"anio\":\"\",\"sexo\":\"\",\"titulado\":\"\",\"periodo\":\"2\",\"tipo_informe\":\"detallado\",\"formato\":\"excel\",\"seccion\":\"\",\"curp_seleccionado\":\"GACV041201MVZRRLA4\"}',NULL,'2025-05-20 14:24:03');
INSERT INTO `historial_consulta` VALUES('2','GOTS040611BH5','Informe por Sección','{\"carrera\":\"\",\"anio\":\"\",\"sexo\":\"\",\"titulado\":\"\",\"periodo\":\"2\",\"tipo_informe\":\"por_seccion\",\"formato\":\"pdf\",\"seccion\":\"2\",\"curp_seleccionado\":\"\"}',NULL,'2025-05-20 14:25:05');
INSERT INTO `historial_consulta` VALUES('3','GOTS040611BH5','Informe por Sección','{\"carrera\":\"Ingenier\\u00eda en Sistemas Computacionales\",\"anio\":\"\",\"sexo\":\"\",\"titulado\":\"\",\"periodo\":\"2\",\"tipo_informe\":\"por_seccion\",\"formato\":\"pdf\",\"seccion\":\"2\",\"curp_seleccionado\":\"\"}',NULL,'2025-05-20 14:26:03');
INSERT INTO `historial_consulta` VALUES('4','GOTS040611BH5','Informe por Sección','{\"carrera\":\"\",\"anio\":\"\",\"sexo\":\"\",\"titulado\":\"\",\"periodo\":\"2\",\"tipo_informe\":\"por_seccion\",\"formato\":\"pdf\",\"seccion\":\"2\",\"curp_seleccionado\":\"\"}',NULL,'2025-05-20 17:27:17');
INSERT INTO `historial_consulta` VALUES('5','GOTS040611BH5','Informe por Sección','{\"carrera\":\"Ingenier\\u00eda en Sistemas Computacionales\",\"anio\":\"\",\"sexo\":\"\",\"titulado\":\"\",\"periodo\":\"2\",\"tipo_informe\":\"por_seccion\",\"formato\":\"pdf\",\"seccion\":\"2\",\"curp_seleccionado\":\"\"}',NULL,'2025-05-20 17:27:28');


DROP TABLE IF EXISTS `modificacion_egresado`;
CREATE TABLE `modificacion_egresado` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RFC` varchar(13) DEFAULT NULL,
  `CURP` varchar(18) DEFAULT NULL,
  `CAMPO_MODIFICADO` varchar(50) DEFAULT NULL,
  `VALOR_ANTERIOR` text DEFAULT NULL,
  `VALOR_NUEVO` text DEFAULT NULL,
  `FECHA` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`),
  KEY `RFC` (`RFC`),
  KEY `CURP` (`CURP`),
  CONSTRAINT `modificacion_egresado_ibfk_1` FOREIGN KEY (`RFC`) REFERENCES `usuario` (`RFC`),
  CONSTRAINT `modificacion_egresado_ibfk_2` FOREIGN KEY (`CURP`) REFERENCES `egresado` (`CURP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



DROP TABLE IF EXISTS `opcion_respuesta`;
CREATE TABLE `opcion_respuesta` (
  `ID_OPCION` int(11) NOT NULL AUTO_INCREMENT,
  `ID_PREGUNTA` int(11) DEFAULT NULL,
  `TEXTO` varchar(255) DEFAULT NULL,
  `VALOR` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID_OPCION`),
  KEY `ID_PREGUNTA` (`ID_PREGUNTA`),
  CONSTRAINT `opcion_respuesta_ibfk_1` FOREIGN KEY (`ID_PREGUNTA`) REFERENCES `pregunta` (`ID_PREGUNTA`)
) ENGINE=InnoDB AUTO_INCREMENT=288 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `opcion_respuesta` VALUES('1','4','Muy Buena','4');
INSERT INTO `opcion_respuesta` VALUES('2','4','Buena','3');
INSERT INTO `opcion_respuesta` VALUES('3','4','Regular','2');
INSERT INTO `opcion_respuesta` VALUES('4','4','Mala','1');
INSERT INTO `opcion_respuesta` VALUES('5','5','Muy Buena','4');
INSERT INTO `opcion_respuesta` VALUES('6','5','Buena','3');
INSERT INTO `opcion_respuesta` VALUES('7','5','Regular','2');
INSERT INTO `opcion_respuesta` VALUES('8','5','Mala','1');
INSERT INTO `opcion_respuesta` VALUES('9','6','Muy Buena','4');
INSERT INTO `opcion_respuesta` VALUES('10','6','Buena','3');
INSERT INTO `opcion_respuesta` VALUES('11','6','Regular','2');
INSERT INTO `opcion_respuesta` VALUES('12','6','Mala','1');
INSERT INTO `opcion_respuesta` VALUES('13','7','Muy Buena','4');
INSERT INTO `opcion_respuesta` VALUES('14','7','Buena','3');
INSERT INTO `opcion_respuesta` VALUES('15','7','Regular','2');
INSERT INTO `opcion_respuesta` VALUES('16','7','Mala','1');
INSERT INTO `opcion_respuesta` VALUES('17','8','Muy Buena','4');
INSERT INTO `opcion_respuesta` VALUES('18','8','Buena','3');
INSERT INTO `opcion_respuesta` VALUES('19','8','Regular','2');
INSERT INTO `opcion_respuesta` VALUES('20','8','Mala','1');
INSERT INTO `opcion_respuesta` VALUES('21','9','Muy Buena','4');
INSERT INTO `opcion_respuesta` VALUES('22','9','Buena','3');
INSERT INTO `opcion_respuesta` VALUES('23','9','Regular','2');
INSERT INTO `opcion_respuesta` VALUES('24','9','Mala','1');
INSERT INTO `opcion_respuesta` VALUES('25','10','Trabaja','1');
INSERT INTO `opcion_respuesta` VALUES('26','10','Estudia','2');
INSERT INTO `opcion_respuesta` VALUES('27','10','Estudia y Trabaja','3');
INSERT INTO `opcion_respuesta` VALUES('28','10','No estudia ni trabaja','4');
INSERT INTO `opcion_respuesta` VALUES('29','11','Especialidad','1');
INSERT INTO `opcion_respuesta` VALUES('30','11','Maestría','2');
INSERT INTO `opcion_respuesta` VALUES('31','11','Doctorado','3');
INSERT INTO `opcion_respuesta` VALUES('32','11','Idiomas','4');
INSERT INTO `opcion_respuesta` VALUES('33','11','Otra','5');
INSERT INTO `opcion_respuesta` VALUES('34','13','Antes de Egresar','1');
INSERT INTO `opcion_respuesta` VALUES('35','13','Menos de seis meses','2');
INSERT INTO `opcion_respuesta` VALUES('36','13','Entre seis meses y un año','3');
INSERT INTO `opcion_respuesta` VALUES('37','13','Más de un año','4');
INSERT INTO `opcion_respuesta` VALUES('38','16','Inglés','1');
INSERT INTO `opcion_respuesta` VALUES('39','16','Francés','2');
INSERT INTO `opcion_respuesta` VALUES('40','16','Alemán','3');
INSERT INTO `opcion_respuesta` VALUES('41','16','Japonés','4');
INSERT INTO `opcion_respuesta` VALUES('42','16','Otro','5');
INSERT INTO `opcion_respuesta` VALUES('43','21','Menos de un año','1');
INSERT INTO `opcion_respuesta` VALUES('44','21','Un año','2');
INSERT INTO `opcion_respuesta` VALUES('45','21','Dos años','3');
INSERT INTO `opcion_respuesta` VALUES('46','21','Tres Años','4');
INSERT INTO `opcion_respuesta` VALUES('47','21','Más de tres años','5');
INSERT INTO `opcion_respuesta` VALUES('48','23','Menos de cinco','1');
INSERT INTO `opcion_respuesta` VALUES('49','23','Entre cinco y siete','2');
INSERT INTO `opcion_respuesta` VALUES('50','23','Entre 8 y 10','3');
INSERT INTO `opcion_respuesta` VALUES('51','23','Más de 10','4');
INSERT INTO `opcion_respuesta` VALUES('52','24','Técnico','1');
INSERT INTO `opcion_respuesta` VALUES('53','24','Supervisor','2');
INSERT INTO `opcion_respuesta` VALUES('54','24','Jefe de área','3');
INSERT INTO `opcion_respuesta` VALUES('55','24','Funcionario','4');
INSERT INTO `opcion_respuesta` VALUES('56','24','Directivo','5');
INSERT INTO `opcion_respuesta` VALUES('57','24','Empresario','6');
INSERT INTO `opcion_respuesta` VALUES('58','25','Base','1');
INSERT INTO `opcion_respuesta` VALUES('59','25','Eventual','2');
INSERT INTO `opcion_respuesta` VALUES('60','25','Contrato','3');
INSERT INTO `opcion_respuesta` VALUES('61','25','Otro','4');
INSERT INTO `opcion_respuesta` VALUES('62','26','0%','0');
INSERT INTO `opcion_respuesta` VALUES('63','26','20%','20');
INSERT INTO `opcion_respuesta` VALUES('64','26','40%','40');
INSERT INTO `opcion_respuesta` VALUES('65','26','60%','60');
INSERT INTO `opcion_respuesta` VALUES('66','26','80%','80');
INSERT INTO `opcion_respuesta` VALUES('67','26','100%','100');
INSERT INTO `opcion_respuesta` VALUES('68','27','Agroindustria','1');
INSERT INTO `opcion_respuesta` VALUES('69','27','Pesquero','2');
INSERT INTO `opcion_respuesta` VALUES('70','27','Minero','3');
INSERT INTO `opcion_respuesta` VALUES('71','27','Otros - Primario','4');
INSERT INTO `opcion_respuesta` VALUES('72','27','Industrial','5');
INSERT INTO `opcion_respuesta` VALUES('73','27','Construcción','6');
INSERT INTO `opcion_respuesta` VALUES('74','27','Petrolero','7');
INSERT INTO `opcion_respuesta` VALUES('75','27','Otros - Secundario','8');
INSERT INTO `opcion_respuesta` VALUES('76','27','Educativo','9');
INSERT INTO `opcion_respuesta` VALUES('77','27','Turismo','10');
INSERT INTO `opcion_respuesta` VALUES('78','27','Comercio','11');
INSERT INTO `opcion_respuesta` VALUES('79','27','Servicios Financieros','12');
INSERT INTO `opcion_respuesta` VALUES('80','27','Otros - Terciario','13');
INSERT INTO `opcion_respuesta` VALUES('81','28','Microempresa (1-30)','1');
INSERT INTO `opcion_respuesta` VALUES('82','28','Pequeña (31-100)','2');
INSERT INTO `opcion_respuesta` VALUES('83','28','Mediana (101-500)','3');
INSERT INTO `opcion_respuesta` VALUES('84','28','Grande (más de 500)','4');
INSERT INTO `opcion_respuesta` VALUES('85','29','Muy eficiente','4');
INSERT INTO `opcion_respuesta` VALUES('86','29','Eficiente','3');
INSERT INTO `opcion_respuesta` VALUES('87','29','Poco eficiente','2');
INSERT INTO `opcion_respuesta` VALUES('88','29','Deficiente','1');
INSERT INTO `opcion_respuesta` VALUES('89','30','Excelente','5');
INSERT INTO `opcion_respuesta` VALUES('90','30','Bueno','4');
INSERT INTO `opcion_respuesta` VALUES('91','30','Regular','3');
INSERT INTO `opcion_respuesta` VALUES('92','30','Malo','2');
INSERT INTO `opcion_respuesta` VALUES('93','30','Pésimo','1');
INSERT INTO `opcion_respuesta` VALUES('94','31','Excelente','5');
INSERT INTO `opcion_respuesta` VALUES('95','31','Bueno','4');
INSERT INTO `opcion_respuesta` VALUES('96','31','Regular','3');
INSERT INTO `opcion_respuesta` VALUES('97','31','Malo','2');
INSERT INTO `opcion_respuesta` VALUES('98','31','Pésimo','1');
INSERT INTO `opcion_respuesta` VALUES('99','54','Compromiso Laboral','1');
INSERT INTO `opcion_respuesta` VALUES('100','54','Falta de Tiempo','2');
INSERT INTO `opcion_respuesta` VALUES('101','54','Falta de Apoyo Institucional','3');
INSERT INTO `opcion_respuesta` VALUES('102','54','Otras','4');
INSERT INTO `opcion_respuesta` VALUES('103','57','0-3 Meses','1');
INSERT INTO `opcion_respuesta` VALUES('104','57','4-6 Meses','2');
INSERT INTO `opcion_respuesta` VALUES('105','57','6 Meses - 1 Año','3');
INSERT INTO `opcion_respuesta` VALUES('106','57','1-5 Años','4');
INSERT INTO `opcion_respuesta` VALUES('107','57','6 años o más','5');
INSERT INTO `opcion_respuesta` VALUES('108','58','Estudio un Posgrado','1');
INSERT INTO `opcion_respuesta` VALUES('109','58','Por razones de salud','2');
INSERT INTO `opcion_respuesta` VALUES('110','58','Ajustes de la empresa','3');
INSERT INTO `opcion_respuesta` VALUES('111','58','No he encontrado un trabajo relacionado','4');
INSERT INTO `opcion_respuesta` VALUES('112','58','Otras','5');
INSERT INTO `opcion_respuesta` VALUES('113','59','Al egresar ya contaba con un trabajo','1');
INSERT INTO `opcion_respuesta` VALUES('114','59','Menos de 6 meses','2');
INSERT INTO `opcion_respuesta` VALUES('115','59','Más de 1 año','3');
INSERT INTO `opcion_respuesta` VALUES('116','59','Aún no lo consigo','4');
INSERT INTO `opcion_respuesta` VALUES('117','61','Sector privado','1');
INSERT INTO `opcion_respuesta` VALUES('118','61','Sector público','2');
INSERT INTO `opcion_respuesta` VALUES('119','61','En empresa propia','3');
INSERT INTO `opcion_respuesta` VALUES('120','61','Otro','4');
INSERT INTO `opcion_respuesta` VALUES('121','62','Dirección o Gerencia','1');
INSERT INTO `opcion_respuesta` VALUES('122','62','Jefatura','2');
INSERT INTO `opcion_respuesta` VALUES('123','62','Supervisión','3');
INSERT INTO `opcion_respuesta` VALUES('124','62','Coordinador','4');
INSERT INTO `opcion_respuesta` VALUES('125','62','Empleado','5');
INSERT INTO `opcion_respuesta` VALUES('126','62','Dueño de empresa','6');
INSERT INTO `opcion_respuesta` VALUES('127','62','Otro','7');
INSERT INTO `opcion_respuesta` VALUES('128','63','Producción','1');
INSERT INTO `opcion_respuesta` VALUES('129','63','Ambiental','2');
INSERT INTO `opcion_respuesta` VALUES('130','63','Seguridad','3');
INSERT INTO `opcion_respuesta` VALUES('131','63','Recursos Financieros','4');
INSERT INTO `opcion_respuesta` VALUES('132','63','Mantenimiento','5');
INSERT INTO `opcion_respuesta` VALUES('133','63','Recursos Humanos','6');
INSERT INTO `opcion_respuesta` VALUES('134','63','Otras','7');
INSERT INTO `opcion_respuesta` VALUES('135','64','Bolsa de trabajo del TecNM/ITVer','1');
INSERT INTO `opcion_respuesta` VALUES('136','64','Anuncio en internet','2');
INSERT INTO `opcion_respuesta` VALUES('137','64','Recomendación de colegas','3');
INSERT INTO `opcion_respuesta` VALUES('138','64','Residencias profesionales','4');
INSERT INTO `opcion_respuesta` VALUES('139','64','Otro','5');
INSERT INTO `opcion_respuesta` VALUES('140','65','Muy satisfecho','4');
INSERT INTO `opcion_respuesta` VALUES('141','65','Satisfecho','3');
INSERT INTO `opcion_respuesta` VALUES('142','65','Poco satisfecho','2');
INSERT INTO `opcion_respuesta` VALUES('143','65','Insatisfecho','1');
INSERT INTO `opcion_respuesta` VALUES('144','66','Totalmente','4');
INSERT INTO `opcion_respuesta` VALUES('145','66','Suficiente','3');
INSERT INTO `opcion_respuesta` VALUES('146','66','Poco','2');
INSERT INTO `opcion_respuesta` VALUES('147','66','Nada','1');
INSERT INTO `opcion_respuesta` VALUES('148','67','Muy satisfecho','4');
INSERT INTO `opcion_respuesta` VALUES('149','67','Satisfecho','3');
INSERT INTO `opcion_respuesta` VALUES('150','67','Poco satisfecho','2');
INSERT INTO `opcion_respuesta` VALUES('151','67','Insatisfecho','1');
INSERT INTO `opcion_respuesta` VALUES('152','68','Manejo de softwares','1');
INSERT INTO `opcion_respuesta` VALUES('153','68','Manejo de Normas Nacionales e Internacionales','2');
INSERT INTO `opcion_respuesta` VALUES('154','68','Evaluación de proyectos de inversión','3');
INSERT INTO `opcion_respuesta` VALUES('155','68','Habilidades directivas','4');
INSERT INTO `opcion_respuesta` VALUES('156','68','Otras','5');
INSERT INTO `opcion_respuesta` VALUES('157','71','Impartiendo un curso o conferencia','1');
INSERT INTO `opcion_respuesta` VALUES('158','71','Apoyar para una visita industrial donde laboras','2');
INSERT INTO `opcion_respuesta` VALUES('159','71','Apoyar a jóvenes para la realización de residencias profesionales','3');
INSERT INTO `opcion_respuesta` VALUES('160','71','Apoyar a jóvenes para realizar investigaciones','4');
INSERT INTO `opcion_respuesta` VALUES('161','71','Apoyar a jóvenes para realizar Educación Dual','5');
INSERT INTO `opcion_respuesta` VALUES('162','71','Donativos en especie','6');
INSERT INTO `opcion_respuesta` VALUES('163','71','Otras','7');
INSERT INTO `opcion_respuesta` VALUES('164','72','Documentos técnicos','1');
INSERT INTO `opcion_respuesta` VALUES('165','72','Planos de Ingeniería','2');
INSERT INTO `opcion_respuesta` VALUES('166','72','Normas Nacionales y/o Internacionales','3');
INSERT INTO `opcion_respuesta` VALUES('167','72','Softwares','4');
INSERT INTO `opcion_respuesta` VALUES('168','72','Otras','5');
INSERT INTO `opcion_respuesta` VALUES('169','74','Aplicada','1');
INSERT INTO `opcion_respuesta` VALUES('170','74','Experimental','2');
INSERT INTO `opcion_respuesta` VALUES('171','74','Documental','3');
INSERT INTO `opcion_respuesta` VALUES('172','74','Descriptiva','4');
INSERT INTO `opcion_respuesta` VALUES('173','74','Otras','5');
INSERT INTO `opcion_respuesta` VALUES('174','78','Asesoría y/o consultoría','1');
INSERT INTO `opcion_respuesta` VALUES('175','78','Peritaje','2');
INSERT INTO `opcion_respuesta` VALUES('176','78','Certificación','3');
INSERT INTO `opcion_respuesta` VALUES('177','78','Ninguno','4');
INSERT INTO `opcion_respuesta` VALUES('178','78','Otros','5');
INSERT INTO `opcion_respuesta` VALUES('179','79','Inglés','1');
INSERT INTO `opcion_respuesta` VALUES('180','79','Francés','2');
INSERT INTO `opcion_respuesta` VALUES('181','79','Alemán','3');
INSERT INTO `opcion_respuesta` VALUES('182','79','Italiano','4');
INSERT INTO `opcion_respuesta` VALUES('183','79','Otras','5');
INSERT INTO `opcion_respuesta` VALUES('184','82','Manuales operativos','1');
INSERT INTO `opcion_respuesta` VALUES('185','82','Procedimientos','2');
INSERT INTO `opcion_respuesta` VALUES('186','82','Lineamientos','3');
INSERT INTO `opcion_respuesta` VALUES('187','82','Informes técnicos','4');
INSERT INTO `opcion_respuesta` VALUES('188','82','Otras','5');
INSERT INTO `opcion_respuesta` VALUES('189','83','Calidad','1');
INSERT INTO `opcion_respuesta` VALUES('190','83','Ambiental','2');
INSERT INTO `opcion_respuesta` VALUES('191','83','Seguridad','3');
INSERT INTO `opcion_respuesta` VALUES('192','83','Otras','4');
INSERT INTO `opcion_respuesta` VALUES('193','86','Aplicar las normas básicas de una empresa','1');
INSERT INTO `opcion_respuesta` VALUES('194','86','Aplicar reglas de cortesía que demuestran respeto por los demás','2');
INSERT INTO `opcion_respuesta` VALUES('195','86','Asumir un comportamiento adecuado para la buena convivencia','3');
INSERT INTO `opcion_respuesta` VALUES('196','86','Establecer normas como guía de conducta','4');
INSERT INTO `opcion_respuesta` VALUES('197','86','Ninguna de las anteriores','5');
INSERT INTO `opcion_respuesta` VALUES('198','14','Bolsa de trabajo del plantel','1');
INSERT INTO `opcion_respuesta` VALUES('199','14','Contactos personales','2');
INSERT INTO `opcion_respuesta` VALUES('200','14','Residencia Profesional','3');
INSERT INTO `opcion_respuesta` VALUES('201','14','Medios masivos de comunicación','4');
INSERT INTO `opcion_respuesta` VALUES('202','14','Otros','5');
INSERT INTO `opcion_respuesta` VALUES('203','15','Competencias laborales','1');
INSERT INTO `opcion_respuesta` VALUES('204','15','Título Profesional','2');
INSERT INTO `opcion_respuesta` VALUES('205','15','Examen de selección','3');
INSERT INTO `opcion_respuesta` VALUES('206','15','Idioma Extranjero','4');
INSERT INTO `opcion_respuesta` VALUES('207','15','Actitudes y habilidades socio-comunicativas (principios y valores)','5');
INSERT INTO `opcion_respuesta` VALUES('208','15','Ninguno','6');
INSERT INTO `opcion_respuesta` VALUES('209','15','Otros','7');
INSERT INTO `opcion_respuesta` VALUES('210','32','Nada','1');
INSERT INTO `opcion_respuesta` VALUES('211','32','Poco','2');
INSERT INTO `opcion_respuesta` VALUES('212','32','Regular','3');
INSERT INTO `opcion_respuesta` VALUES('213','32','Mucho','4');
INSERT INTO `opcion_respuesta` VALUES('214','32','Bastante','5');
INSERT INTO `opcion_respuesta` VALUES('215','33','Nada','1');
INSERT INTO `opcion_respuesta` VALUES('216','33','Poco','2');
INSERT INTO `opcion_respuesta` VALUES('217','33','Regular','3');
INSERT INTO `opcion_respuesta` VALUES('218','33','Mucho','4');
INSERT INTO `opcion_respuesta` VALUES('219','33','Bastante','5');
INSERT INTO `opcion_respuesta` VALUES('220','34','Nada','1');
INSERT INTO `opcion_respuesta` VALUES('221','34','Poco','2');
INSERT INTO `opcion_respuesta` VALUES('222','34','Regular','3');
INSERT INTO `opcion_respuesta` VALUES('223','34','Mucho','4');
INSERT INTO `opcion_respuesta` VALUES('224','34','Bastante','5');
INSERT INTO `opcion_respuesta` VALUES('225','35','Nada','1');
INSERT INTO `opcion_respuesta` VALUES('226','35','Nada','2');
INSERT INTO `opcion_respuesta` VALUES('227','35','Regular','3');
INSERT INTO `opcion_respuesta` VALUES('228','35','Mucho','4');
INSERT INTO `opcion_respuesta` VALUES('229','35','Bastante','5');
INSERT INTO `opcion_respuesta` VALUES('230','36','Nada','1');
INSERT INTO `opcion_respuesta` VALUES('231','36','Poco','2');
INSERT INTO `opcion_respuesta` VALUES('232','36','Regular','3');
INSERT INTO `opcion_respuesta` VALUES('233','36','Mucho','4');
INSERT INTO `opcion_respuesta` VALUES('234','36','Bastante','5');
INSERT INTO `opcion_respuesta` VALUES('235','37','Nada','1');
INSERT INTO `opcion_respuesta` VALUES('236','37','Poco','2');
INSERT INTO `opcion_respuesta` VALUES('237','37','Regular','3');
INSERT INTO `opcion_respuesta` VALUES('238','37','Mucho','4');
INSERT INTO `opcion_respuesta` VALUES('239','37','Bastante','5');
INSERT INTO `opcion_respuesta` VALUES('240','38','Nada','1');
INSERT INTO `opcion_respuesta` VALUES('241','38','Poco','2');
INSERT INTO `opcion_respuesta` VALUES('242','38','Regular','3');
INSERT INTO `opcion_respuesta` VALUES('243','38','Mucho','4');
INSERT INTO `opcion_respuesta` VALUES('244','38','Bastante','5');
INSERT INTO `opcion_respuesta` VALUES('245','39','Nada','1');
INSERT INTO `opcion_respuesta` VALUES('246','39','Poco','2');
INSERT INTO `opcion_respuesta` VALUES('247','39','Regular','3');
INSERT INTO `opcion_respuesta` VALUES('248','39','Mucho','4');
INSERT INTO `opcion_respuesta` VALUES('249','39','Bastante','5');
INSERT INTO `opcion_respuesta` VALUES('250','40','Nada','1');
INSERT INTO `opcion_respuesta` VALUES('251','40','Poco','2');
INSERT INTO `opcion_respuesta` VALUES('252','40','Regular','3');
INSERT INTO `opcion_respuesta` VALUES('253','40','Mucho','4');
INSERT INTO `opcion_respuesta` VALUES('254','40','Bastante','5');
INSERT INTO `opcion_respuesta` VALUES('255','41','Nada','1');
INSERT INTO `opcion_respuesta` VALUES('256','41','Poco','2');
INSERT INTO `opcion_respuesta` VALUES('257','41','Regular','3');
INSERT INTO `opcion_respuesta` VALUES('258','41','Mucho','4');
INSERT INTO `opcion_respuesta` VALUES('259','41','Bastante','5');
INSERT INTO `opcion_respuesta` VALUES('260','42','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('261','42','No','2');
INSERT INTO `opcion_respuesta` VALUES('262','44','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('263','44','No','2');
INSERT INTO `opcion_respuesta` VALUES('264','46','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('265','46','No','2');
INSERT INTO `opcion_respuesta` VALUES('266','48','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('267','48','No','2');
INSERT INTO `opcion_respuesta` VALUES('268','50','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('269','50','No','2');
INSERT INTO `opcion_respuesta` VALUES('270','55','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('271','55','No','2');
INSERT INTO `opcion_respuesta` VALUES('272','56','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('273','56','No','2');
INSERT INTO `opcion_respuesta` VALUES('274','69','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('275','69','No','2');
INSERT INTO `opcion_respuesta` VALUES('276','70','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('277','70','No','2');
INSERT INTO `opcion_respuesta` VALUES('278','73','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('279','73','No','2');
INSERT INTO `opcion_respuesta` VALUES('280','75','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('281','75','No','2');
INSERT INTO `opcion_respuesta` VALUES('282','76','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('283','76','No','2');
INSERT INTO `opcion_respuesta` VALUES('284','80','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('285','80','No','2');
INSERT INTO `opcion_respuesta` VALUES('286','84','Sí','1');
INSERT INTO `opcion_respuesta` VALUES('287','84','No','2');


DROP TABLE IF EXISTS `periodo_encuesta`;
CREATE TABLE `periodo_encuesta` (
  `ID_PERIODO` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE` varchar(100) DEFAULT NULL,
  `FECHA_INICIO` date DEFAULT NULL,
  `FECHA_FIN` date DEFAULT NULL,
  `ACTIVO` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`ID_PERIODO`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `periodo_encuesta` VALUES('1','Encuesta de Seguimiento de Egresados AGO/DIC 2024','2024-11-01','2024-11-30','0');
INSERT INTO `periodo_encuesta` VALUES('2','Encuesta de Seguimiento de Egresados ENE/JUN 2025','2025-05-01','2025-05-31','1');


DROP TABLE IF EXISTS `pregunta`;
CREATE TABLE `pregunta` (
  `ID_PREGUNTA` int(11) NOT NULL AUTO_INCREMENT,
  `ID_SECCION` int(11) DEFAULT NULL,
  `TEXTO` text DEFAULT NULL,
  `TIPO` enum('texto','multiple','opcion','escala','boolean') DEFAULT NULL,
  `OBLIGATORIA` tinyint(1) DEFAULT NULL,
  `ORDEN` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID_PREGUNTA`),
  KEY `ID_SECCION` (`ID_SECCION`),
  CONSTRAINT `pregunta_ibfk_1` FOREIGN KEY (`ID_SECCION`) REFERENCES `seccion` (`ID_SECCION`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pregunta` VALUES('0','6','¿Cuáles organizaciones sociales?','texto','0','2');
INSERT INTO `pregunta` VALUES('1','1','Dominio de idioma extranjero: Inglés (%)','texto','0','1');
INSERT INTO `pregunta` VALUES('2','1','Dominio de otro idioma (especificar y %)','texto','0','2');
INSERT INTO `pregunta` VALUES('3','1','Manejo de paquetes computacionales (especificar)','texto','0','3');
INSERT INTO `pregunta` VALUES('4','2','Calidad de los docentes','opcion','1','1');
INSERT INTO `pregunta` VALUES('5','2','Plan de estudios','opcion','1','2');
INSERT INTO `pregunta` VALUES('6','2','Oportunidad de participar en proyectos de investigación y desarrollo','opcion','1','3');
INSERT INTO `pregunta` VALUES('7','2','Énfasis en investigación dentro del proceso de enseñanza','opcion','1','4');
INSERT INTO `pregunta` VALUES('8','2','Satisfacción con las condiciones de estudio (infraestructura)','opcion','1','5');
INSERT INTO `pregunta` VALUES('9','2','Experiencia obtenida a través de la residencia profesional','opcion','1','6');
INSERT INTO `pregunta` VALUES('10','3','Actividad actual (laboral/académica)','opcion','1','1');
INSERT INTO `pregunta` VALUES('11','3','Si estudia, ¿qué tipo de estudio realiza?','opcion','0','2');
INSERT INTO `pregunta` VALUES('12','3','Especialidad e institución donde estudia','texto','0','3');
INSERT INTO `pregunta` VALUES('13','3','Tiempo para conseguir el primer empleo','opcion','0','4');
INSERT INTO `pregunta` VALUES('14','3','Medio para obtener el empleo','opcion','0','5');
INSERT INTO `pregunta` VALUES('15','3','Requisitos de contratación','multiple','0','6');
INSERT INTO `pregunta` VALUES('16','3','Idiomas que usa en el trabajo','multiple','0','7');
INSERT INTO `pregunta` VALUES('17','3','Porcentaje de uso del idioma en el trabajo - Hablar','texto','0','8');
INSERT INTO `pregunta` VALUES('18','3','Porcentaje de uso del idioma en el trabajo - Escribir','texto','0','9');
INSERT INTO `pregunta` VALUES('19','3','Porcentaje de uso del idioma en el trabajo - Leer','texto','0','10');
INSERT INTO `pregunta` VALUES('20','3','Porcentaje de uso del idioma en el trabajo - Escuchar','texto','0','11');
INSERT INTO `pregunta` VALUES('21','3','Antigüedad en el empleo','opcion','0','12');
INSERT INTO `pregunta` VALUES('22','3','Año de ingreso laboral','texto','0','13');
INSERT INTO `pregunta` VALUES('23','3','Ingreso (salario mínimo diario)','opcion','0','14');
INSERT INTO `pregunta` VALUES('24','3','Nivel jerárquico en el trabajo','opcion','0','15');
INSERT INTO `pregunta` VALUES('25','3','Condición de trabajo','opcion','0','16');
INSERT INTO `pregunta` VALUES('26','3','Relación del trabajo con su formación (%)','opcion','0','17');
INSERT INTO `pregunta` VALUES('27','3','Sector económico de la empresa','multiple','0','18');
INSERT INTO `pregunta` VALUES('28','3','Tamaño de la empresa u organización','opcion','0','19');
INSERT INTO `pregunta` VALUES('29','4','Eficiencia para realizar actividades laborales en relación con su formación académica','opcion','1','1');
INSERT INTO `pregunta` VALUES('30','4','Formación académica respecto a su desempeño laboral','opcion','1','2');
INSERT INTO `pregunta` VALUES('31','4','Utilidad de las residencias profesionales o prácticas para su desarrollo profesional','opcion','1','3');
INSERT INTO `pregunta` VALUES('32','4','Área o Campo de Estudio','opcion','1','4');
INSERT INTO `pregunta` VALUES('33','4','Titulación','opcion','1','5');
INSERT INTO `pregunta` VALUES('34','4','Experiencia Laboral/práctica (antes de egresar)','opcion','1','6');
INSERT INTO `pregunta` VALUES('35','4','Competencia laboral (resolución de problemas, creatividad, etc.)','opcion','1','7');
INSERT INTO `pregunta` VALUES('36','4','Posicionamiento de la Institución de Egreso','opcion','1','8');
INSERT INTO `pregunta` VALUES('37','4','Conocimiento de Idiomas Extranjeros','opcion','1','9');
INSERT INTO `pregunta` VALUES('38','4','Recomendaciones/Referencias','opcion','1','10');
INSERT INTO `pregunta` VALUES('39','4','Personalidad/Actitudes','opcion','1','11');
INSERT INTO `pregunta` VALUES('40','4','Capacidad de Liderazgo','opcion','1','12');
INSERT INTO `pregunta` VALUES('41','4','Otros Aspectos Valorados por la Empresa','opcion','1','13');
INSERT INTO `pregunta` VALUES('42','5','Le gustaría tomar cursos de actualización:','opcion','1','1');
INSERT INTO `pregunta` VALUES('43','5','¿Cuáles cursos le interesarían?','texto','0','2');
INSERT INTO `pregunta` VALUES('44','5','Le gustaría tomar algún posgrado:','opcion','1','3');
INSERT INTO `pregunta` VALUES('45','5','¿Qué posgrado le interesaría?','texto','0','4');
INSERT INTO `pregunta` VALUES('46','6','Pertenece a organizaciones sociales:','opcion','1','1');
INSERT INTO `pregunta` VALUES('48','6','Pertenece a organismos de profesionistas:','opcion','1','3');
INSERT INTO `pregunta` VALUES('49','6','¿Cuál organismo de profesionistas?','texto','0','4');
INSERT INTO `pregunta` VALUES('50','6','Pertenece a la asociación de egresados:','opcion','1','5');
INSERT INTO `pregunta` VALUES('51','7','Opinión o recomendación para mejorar la formación profesional de un egresado de su carrera','texto','0','1');
INSERT INTO `pregunta` VALUES('52','8','¿Cuáles son tus redes sociales (Facebook, Instagram, Twitter)?','texto','1','1');
INSERT INTO `pregunta` VALUES('53','8','Fecha de Ingreso','texto','1','2');
INSERT INTO `pregunta` VALUES('54','8','En caso de NO estar Titulado, ¿Cuál ha sido la razón?','opcion','0','3');
INSERT INTO `pregunta` VALUES('55','8','¿Tu trabajo actual tiene relación con la carrera que estudiaste?','boolean','1','4');
INSERT INTO `pregunta` VALUES('56','9','¿Trabajas actualmente?','boolean','1','1');
INSERT INTO `pregunta` VALUES('57','9','¿Cuál es la antigüedad de tu empleo actual?','opcion','0','2');
INSERT INTO `pregunta` VALUES('58','9','Si no trabajas actualmente, ¿cuál es la razón?','opcion','0','3');
INSERT INTO `pregunta` VALUES('59','9','¿Después de egresar, en cuánto tiempo conseguiste trabajo relacionado con tu carrera?','opcion','1','4');
INSERT INTO `pregunta` VALUES('60','9','Si aún no consigues trabajo relacionado con tu carrera, ¿cuál es la razón?','texto','0','5');
INSERT INTO `pregunta` VALUES('61','9','¿En qué tipo de sector trabajas?','opcion','0','6');
INSERT INTO `pregunta` VALUES('62','9','¿Cuál es tu rol en el trabajo actual?','opcion','0','7');
INSERT INTO `pregunta` VALUES('63','9','¿En qué área te desempeñas dentro de la empresa?','opcion','0','8');
INSERT INTO `pregunta` VALUES('64','9','¿Cuál fue el medio principal para conseguir tu primer trabajo después de egresar?','opcion','0','9');
INSERT INTO `pregunta` VALUES('65','9','¿Cuál es tu grado de satisfacción en tu trabajo actual?','escala','0','10');
INSERT INTO `pregunta` VALUES('66','10','¿Cómo consideras que las competencias adquiridas en la institución ayudan al desarrollo de tu trabajo?','opcion','1','1');
INSERT INTO `pregunta` VALUES('67','10','¿Cuál es el grado de satisfacción de la carrera que estudiaste?','opcion','1','2');
INSERT INTO `pregunta` VALUES('68','10','¿Qué sugieres reforzar y/o actualizar respecto a los contenidos del programa educativo?','multiple','0','3');
INSERT INTO `pregunta` VALUES('69','11','¿La institución se ha contactado contigo anteriormente?','boolean','0','1');
INSERT INTO `pregunta` VALUES('70','11','¿Te gustaría participar con la institución aportando tu experiencia profesional?','boolean','0','2');
INSERT INTO `pregunta` VALUES('71','11','¿Cómo podrías participar con la institución?','multiple','0','3');
INSERT INTO `pregunta` VALUES('72','12','¿Qué herramientas utilizas en tu desempeño laboral?','multiple','1','1');
INSERT INTO `pregunta` VALUES('73','12','¿Colaboras actualmente en proyectos de investigación y/o desarrollo?','boolean','1','2');
INSERT INTO `pregunta` VALUES('74','12','Si colaboras, ¿qué tipo de investigación realizas?','opcion','0','3');
INSERT INTO `pregunta` VALUES('75','12','¿Perteneces o participas en redes de colaboración?','boolean','0','4');
INSERT INTO `pregunta` VALUES('76','12','¿Cuentas con certificaciones vigentes nacionales y/o internacionales?','boolean','0','5');
INSERT INTO `pregunta` VALUES('77','12','Especifica tus certificaciones vigentes (si aplica)','texto','0','6');
INSERT INTO `pregunta` VALUES('78','12','¿Ofreces alguno de los siguientes servicios?','multiple','0','7');
INSERT INTO `pregunta` VALUES('79','12','¿Qué lenguas extranjeras utilizas en tu actividad laboral?','multiple','0','8');
INSERT INTO `pregunta` VALUES('80','12','¿Has publicado en alguna revista científica o de divulgación?','boolean','0','9');
INSERT INTO `pregunta` VALUES('81','12','Especifica tus publicaciones (si aplica)','texto','0','10');
INSERT INTO `pregunta` VALUES('82','12','¿En cuál de los siguientes documentos has participado en su elaboración?','multiple','0','11');
INSERT INTO `pregunta` VALUES('83','12','¿Qué sistema de gestión aplicas en tu actividad laboral?','multiple','0','12');
INSERT INTO `pregunta` VALUES('84','12','¿Perteneces a alguna asociación profesional relacionada con tu carrera?','boolean','0','13');
INSERT INTO `pregunta` VALUES('85','12','Especifica a qué asociación perteneces (si aplica)','texto','0','14');
INSERT INTO `pregunta` VALUES('86','12','Desde tu punto de vista, el aporte de la ética en un ambiente laboral consiste en:','multiple','1','15');


DROP TABLE IF EXISTS `respuesta`;
CREATE TABLE `respuesta` (
  `ID_RESPUESTA` int(11) NOT NULL AUTO_INCREMENT,
  `ID_CUESTIONARIO` int(11) DEFAULT NULL,
  `ID_PREGUNTA` int(11) DEFAULT NULL,
  `ID_OPCION` int(11) DEFAULT NULL,
  `RESPUESTA_TEXTO` text DEFAULT NULL,
  PRIMARY KEY (`ID_RESPUESTA`),
  KEY `ID_CUESTIONARIO` (`ID_CUESTIONARIO`),
  KEY `ID_PREGUNTA` (`ID_PREGUNTA`),
  KEY `ID_OPCION` (`ID_OPCION`),
  CONSTRAINT `respuesta_ibfk_1` FOREIGN KEY (`ID_CUESTIONARIO`) REFERENCES `cuestionario_respuesta` (`ID_CUESTIONARIO`),
  CONSTRAINT `respuesta_ibfk_2` FOREIGN KEY (`ID_PREGUNTA`) REFERENCES `pregunta` (`ID_PREGUNTA`),
  CONSTRAINT `respuesta_ibfk_3` FOREIGN KEY (`ID_OPCION`) REFERENCES `opcion_respuesta` (`ID_OPCION`)
) ENGINE=InnoDB AUTO_INCREMENT=850 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `respuesta` VALUES('1','1','1',NULL,'40');
INSERT INTO `respuesta` VALUES('2','1','2',NULL,'50');
INSERT INTO `respuesta` VALUES('3','1','3',NULL,'TODO LA PAQUETERIA DE MICROSOFT');
INSERT INTO `respuesta` VALUES('4','1','4','1',NULL);
INSERT INTO `respuesta` VALUES('5','1','5','6',NULL);
INSERT INTO `respuesta` VALUES('6','1','6','11',NULL);
INSERT INTO `respuesta` VALUES('7','1','7','13',NULL);
INSERT INTO `respuesta` VALUES('8','1','8','17',NULL);
INSERT INTO `respuesta` VALUES('9','1','9','22',NULL);
INSERT INTO `respuesta` VALUES('10','1','10','25',NULL);
INSERT INTO `respuesta` VALUES('11','1','13','35',NULL);
INSERT INTO `respuesta` VALUES('12','1','14','199',NULL);
INSERT INTO `respuesta` VALUES('13','1','15','204',NULL);
INSERT INTO `respuesta` VALUES('14','1','15','205',NULL);
INSERT INTO `respuesta` VALUES('15','1','17',NULL,'50');
INSERT INTO `respuesta` VALUES('16','1','18',NULL,'60');
INSERT INTO `respuesta` VALUES('17','1','19',NULL,'70');
INSERT INTO `respuesta` VALUES('18','1','20',NULL,'80');
INSERT INTO `respuesta` VALUES('19','1','21','43',NULL);
INSERT INTO `respuesta` VALUES('20','1','22',NULL,'2024');
INSERT INTO `respuesta` VALUES('21','1','23','50',NULL);
INSERT INTO `respuesta` VALUES('22','1','24','54',NULL);
INSERT INTO `respuesta` VALUES('23','1','25','60',NULL);
INSERT INTO `respuesta` VALUES('24','1','26','66',NULL);
INSERT INTO `respuesta` VALUES('25','1','27','72',NULL);
INSERT INTO `respuesta` VALUES('26','1','28','83',NULL);
INSERT INTO `respuesta` VALUES('27','1','29','86',NULL);
INSERT INTO `respuesta` VALUES('28','1','30','90',NULL);
INSERT INTO `respuesta` VALUES('29','1','31','96',NULL);
INSERT INTO `respuesta` VALUES('30','1','32','214',NULL);
INSERT INTO `respuesta` VALUES('31','1','33','219',NULL);
INSERT INTO `respuesta` VALUES('32','1','34','224',NULL);
INSERT INTO `respuesta` VALUES('33','1','35','229',NULL);
INSERT INTO `respuesta` VALUES('34','1','36','234',NULL);
INSERT INTO `respuesta` VALUES('35','1','37','239',NULL);
INSERT INTO `respuesta` VALUES('36','1','38','244',NULL);
INSERT INTO `respuesta` VALUES('37','1','39','249',NULL);
INSERT INTO `respuesta` VALUES('38','1','40','254',NULL);
INSERT INTO `respuesta` VALUES('39','1','41','259',NULL);
INSERT INTO `respuesta` VALUES('40','1','42','260',NULL);
INSERT INTO `respuesta` VALUES('41','1','43',NULL,'CURSOS DE SISTEMAS OPERATIVOS');
INSERT INTO `respuesta` VALUES('42','1','44','262',NULL);
INSERT INTO `respuesta` VALUES('43','1','45',NULL,'EN REDES DE TELECOMUNICACION');
INSERT INTO `respuesta` VALUES('44','1','46','265',NULL);
INSERT INTO `respuesta` VALUES('45','1','48','267',NULL);
INSERT INTO `respuesta` VALUES('46','1','50','269',NULL);
INSERT INTO `respuesta` VALUES('47','1','51',NULL,'CREO QUE POR EL MOMENTO ESTA TODO BIEN');
INSERT INTO `respuesta` VALUES('48','2','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('49','2','2',NULL,'0');
INSERT INTO `respuesta` VALUES('50','2','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('51','3','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('52','3','2',NULL,'0');
INSERT INTO `respuesta` VALUES('53','3','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('54','4','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('55','4','2',NULL,'0');
INSERT INTO `respuesta` VALUES('56','4','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('57','5','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('58','5','2',NULL,'0');
INSERT INTO `respuesta` VALUES('59','5','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('60','6','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('61','6','2',NULL,'0');
INSERT INTO `respuesta` VALUES('62','6','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('63','7','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('64','7','2',NULL,'0');
INSERT INTO `respuesta` VALUES('65','7','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('66','8','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('67','8','2',NULL,'0');
INSERT INTO `respuesta` VALUES('68','8','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('69','9','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('70','9','2',NULL,'0');
INSERT INTO `respuesta` VALUES('71','9','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('72','10','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('73','10','2',NULL,'0');
INSERT INTO `respuesta` VALUES('74','10','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('75','11','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('76','11','2',NULL,'0');
INSERT INTO `respuesta` VALUES('77','11','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('78','12','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('79','12','2',NULL,'0');
INSERT INTO `respuesta` VALUES('80','12','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('81','13','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('82','13','2',NULL,'0');
INSERT INTO `respuesta` VALUES('83','13','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('84','14','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('85','14','2',NULL,'0');
INSERT INTO `respuesta` VALUES('86','14','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('87','15','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('88','15','2',NULL,'0');
INSERT INTO `respuesta` VALUES('89','15','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('90','16','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('91','16','2',NULL,'0');
INSERT INTO `respuesta` VALUES('92','16','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('93','17','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('94','17','2',NULL,'0');
INSERT INTO `respuesta` VALUES('95','17','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('96','18','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('97','18','2',NULL,'0');
INSERT INTO `respuesta` VALUES('98','18','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('99','19','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('100','19','2',NULL,'0');
INSERT INTO `respuesta` VALUES('101','19','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('102','20','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('103','20','2',NULL,'0');
INSERT INTO `respuesta` VALUES('104','20','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('105','21','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('106','21','2',NULL,'0');
INSERT INTO `respuesta` VALUES('107','21','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('108','22','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('109','22','2',NULL,'0');
INSERT INTO `respuesta` VALUES('110','22','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('111','23','1',NULL,'1');
INSERT INTO `respuesta` VALUES('112','23','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('113','23','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('114','24','1',NULL,'1');
INSERT INTO `respuesta` VALUES('115','24','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('116','24','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('117','25','1',NULL,'1');
INSERT INTO `respuesta` VALUES('118','25','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('119','25','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('120','26','1',NULL,'1');
INSERT INTO `respuesta` VALUES('121','26','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('122','26','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('123','27','1',NULL,'1');
INSERT INTO `respuesta` VALUES('124','27','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('125','27','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('126','28','1',NULL,'1');
INSERT INTO `respuesta` VALUES('127','28','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('128','28','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('129','29','1',NULL,'1');
INSERT INTO `respuesta` VALUES('130','29','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('131','29','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('132','30','1',NULL,'1');
INSERT INTO `respuesta` VALUES('133','30','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('134','30','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('135','31','1',NULL,'1');
INSERT INTO `respuesta` VALUES('136','31','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('137','31','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('138','32','1',NULL,'1');
INSERT INTO `respuesta` VALUES('139','32','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('140','32','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('141','2','4','1',NULL);
INSERT INTO `respuesta` VALUES('142','2','5','5',NULL);
INSERT INTO `respuesta` VALUES('143','2','6','10',NULL);
INSERT INTO `respuesta` VALUES('144','2','7','14',NULL);
INSERT INTO `respuesta` VALUES('145','2','8','18',NULL);
INSERT INTO `respuesta` VALUES('146','2','9','22',NULL);
INSERT INTO `respuesta` VALUES('147','3','4','1',NULL);
INSERT INTO `respuesta` VALUES('148','3','5','5',NULL);
INSERT INTO `respuesta` VALUES('149','3','6','10',NULL);
INSERT INTO `respuesta` VALUES('150','3','7','14',NULL);
INSERT INTO `respuesta` VALUES('151','3','8','18',NULL);
INSERT INTO `respuesta` VALUES('152','3','9','22',NULL);
INSERT INTO `respuesta` VALUES('153','4','4','1',NULL);
INSERT INTO `respuesta` VALUES('154','4','5','5',NULL);
INSERT INTO `respuesta` VALUES('155','4','6','10',NULL);
INSERT INTO `respuesta` VALUES('156','4','7','14',NULL);
INSERT INTO `respuesta` VALUES('157','4','8','18',NULL);
INSERT INTO `respuesta` VALUES('158','4','9','22',NULL);
INSERT INTO `respuesta` VALUES('159','5','4','1',NULL);
INSERT INTO `respuesta` VALUES('160','5','5','5',NULL);
INSERT INTO `respuesta` VALUES('161','5','6','10',NULL);
INSERT INTO `respuesta` VALUES('162','5','7','14',NULL);
INSERT INTO `respuesta` VALUES('163','5','8','18',NULL);
INSERT INTO `respuesta` VALUES('164','5','9','22',NULL);
INSERT INTO `respuesta` VALUES('165','6','4','1',NULL);
INSERT INTO `respuesta` VALUES('166','6','5','5',NULL);
INSERT INTO `respuesta` VALUES('167','6','6','10',NULL);
INSERT INTO `respuesta` VALUES('168','6','7','14',NULL);
INSERT INTO `respuesta` VALUES('169','6','8','18',NULL);
INSERT INTO `respuesta` VALUES('170','6','9','22',NULL);
INSERT INTO `respuesta` VALUES('171','7','4','1',NULL);
INSERT INTO `respuesta` VALUES('172','7','5','5',NULL);
INSERT INTO `respuesta` VALUES('173','7','6','10',NULL);
INSERT INTO `respuesta` VALUES('174','7','7','14',NULL);
INSERT INTO `respuesta` VALUES('175','7','8','18',NULL);
INSERT INTO `respuesta` VALUES('176','7','9','22',NULL);
INSERT INTO `respuesta` VALUES('177','8','4','1',NULL);
INSERT INTO `respuesta` VALUES('178','8','5','5',NULL);
INSERT INTO `respuesta` VALUES('179','8','6','10',NULL);
INSERT INTO `respuesta` VALUES('180','8','7','14',NULL);
INSERT INTO `respuesta` VALUES('181','8','8','18',NULL);
INSERT INTO `respuesta` VALUES('182','8','9','22',NULL);
INSERT INTO `respuesta` VALUES('183','9','4','1',NULL);
INSERT INTO `respuesta` VALUES('184','9','5','5',NULL);
INSERT INTO `respuesta` VALUES('185','9','6','10',NULL);
INSERT INTO `respuesta` VALUES('186','9','7','14',NULL);
INSERT INTO `respuesta` VALUES('187','9','8','18',NULL);
INSERT INTO `respuesta` VALUES('188','9','9','22',NULL);
INSERT INTO `respuesta` VALUES('189','10','4','1',NULL);
INSERT INTO `respuesta` VALUES('190','10','5','5',NULL);
INSERT INTO `respuesta` VALUES('191','10','6','10',NULL);
INSERT INTO `respuesta` VALUES('192','10','7','14',NULL);
INSERT INTO `respuesta` VALUES('193','10','8','18',NULL);
INSERT INTO `respuesta` VALUES('194','10','9','22',NULL);
INSERT INTO `respuesta` VALUES('195','11','4','1',NULL);
INSERT INTO `respuesta` VALUES('196','11','5','5',NULL);
INSERT INTO `respuesta` VALUES('197','11','6','10',NULL);
INSERT INTO `respuesta` VALUES('198','11','7','14',NULL);
INSERT INTO `respuesta` VALUES('199','11','8','18',NULL);
INSERT INTO `respuesta` VALUES('200','11','9','22',NULL);
INSERT INTO `respuesta` VALUES('201','12','4','3',NULL);
INSERT INTO `respuesta` VALUES('202','12','5','7',NULL);
INSERT INTO `respuesta` VALUES('203','12','6','11',NULL);
INSERT INTO `respuesta` VALUES('204','12','7','15',NULL);
INSERT INTO `respuesta` VALUES('205','12','8','19',NULL);
INSERT INTO `respuesta` VALUES('206','12','9','23',NULL);
INSERT INTO `respuesta` VALUES('207','13','4','3',NULL);
INSERT INTO `respuesta` VALUES('208','13','5','7',NULL);
INSERT INTO `respuesta` VALUES('209','13','6','11',NULL);
INSERT INTO `respuesta` VALUES('210','13','7','15',NULL);
INSERT INTO `respuesta` VALUES('211','13','8','19',NULL);
INSERT INTO `respuesta` VALUES('212','13','9','23',NULL);
INSERT INTO `respuesta` VALUES('213','14','4','3',NULL);
INSERT INTO `respuesta` VALUES('214','14','5','7',NULL);
INSERT INTO `respuesta` VALUES('215','14','6','11',NULL);
INSERT INTO `respuesta` VALUES('216','14','7','15',NULL);
INSERT INTO `respuesta` VALUES('217','14','8','19',NULL);
INSERT INTO `respuesta` VALUES('218','14','9','23',NULL);
INSERT INTO `respuesta` VALUES('219','15','4','3',NULL);
INSERT INTO `respuesta` VALUES('220','15','5','7',NULL);
INSERT INTO `respuesta` VALUES('221','15','6','11',NULL);
INSERT INTO `respuesta` VALUES('222','15','7','15',NULL);
INSERT INTO `respuesta` VALUES('223','15','8','19',NULL);
INSERT INTO `respuesta` VALUES('224','15','9','23',NULL);
INSERT INTO `respuesta` VALUES('225','16','4','3',NULL);
INSERT INTO `respuesta` VALUES('226','16','5','7',NULL);
INSERT INTO `respuesta` VALUES('227','16','6','11',NULL);
INSERT INTO `respuesta` VALUES('228','16','7','15',NULL);
INSERT INTO `respuesta` VALUES('229','16','8','19',NULL);
INSERT INTO `respuesta` VALUES('230','16','9','23',NULL);
INSERT INTO `respuesta` VALUES('231','17','4','3',NULL);
INSERT INTO `respuesta` VALUES('232','17','5','7',NULL);
INSERT INTO `respuesta` VALUES('233','17','6','11',NULL);
INSERT INTO `respuesta` VALUES('234','17','7','15',NULL);
INSERT INTO `respuesta` VALUES('235','17','8','19',NULL);
INSERT INTO `respuesta` VALUES('236','17','9','23',NULL);
INSERT INTO `respuesta` VALUES('237','18','4','3',NULL);
INSERT INTO `respuesta` VALUES('238','18','5','7',NULL);
INSERT INTO `respuesta` VALUES('239','18','6','11',NULL);
INSERT INTO `respuesta` VALUES('240','18','7','15',NULL);
INSERT INTO `respuesta` VALUES('241','18','8','19',NULL);
INSERT INTO `respuesta` VALUES('242','18','9','23',NULL);
INSERT INTO `respuesta` VALUES('243','19','4','3',NULL);
INSERT INTO `respuesta` VALUES('244','19','5','7',NULL);
INSERT INTO `respuesta` VALUES('245','19','6','11',NULL);
INSERT INTO `respuesta` VALUES('246','19','7','15',NULL);
INSERT INTO `respuesta` VALUES('247','19','8','19',NULL);
INSERT INTO `respuesta` VALUES('248','19','9','23',NULL);
INSERT INTO `respuesta` VALUES('249','20','4','3',NULL);
INSERT INTO `respuesta` VALUES('250','20','5','7',NULL);
INSERT INTO `respuesta` VALUES('251','20','6','11',NULL);
INSERT INTO `respuesta` VALUES('252','20','7','15',NULL);
INSERT INTO `respuesta` VALUES('253','20','8','19',NULL);
INSERT INTO `respuesta` VALUES('254','20','9','23',NULL);
INSERT INTO `respuesta` VALUES('255','21','4','3',NULL);
INSERT INTO `respuesta` VALUES('256','21','5','7',NULL);
INSERT INTO `respuesta` VALUES('257','21','6','11',NULL);
INSERT INTO `respuesta` VALUES('258','21','7','15',NULL);
INSERT INTO `respuesta` VALUES('259','21','8','19',NULL);
INSERT INTO `respuesta` VALUES('260','21','9','23',NULL);
INSERT INTO `respuesta` VALUES('261','22','4','3',NULL);
INSERT INTO `respuesta` VALUES('262','22','5','7',NULL);
INSERT INTO `respuesta` VALUES('263','22','6','11',NULL);
INSERT INTO `respuesta` VALUES('264','22','7','15',NULL);
INSERT INTO `respuesta` VALUES('265','22','8','19',NULL);
INSERT INTO `respuesta` VALUES('266','22','9','23',NULL);
INSERT INTO `respuesta` VALUES('267','23','4','4',NULL);
INSERT INTO `respuesta` VALUES('268','23','5','8',NULL);
INSERT INTO `respuesta` VALUES('269','23','6','12',NULL);
INSERT INTO `respuesta` VALUES('270','23','7','16',NULL);
INSERT INTO `respuesta` VALUES('271','23','8','20',NULL);
INSERT INTO `respuesta` VALUES('272','23','9','24',NULL);
INSERT INTO `respuesta` VALUES('273','24','4','4',NULL);
INSERT INTO `respuesta` VALUES('274','24','5','8',NULL);
INSERT INTO `respuesta` VALUES('275','24','6','12',NULL);
INSERT INTO `respuesta` VALUES('276','24','7','16',NULL);
INSERT INTO `respuesta` VALUES('277','24','8','20',NULL);
INSERT INTO `respuesta` VALUES('278','24','9','24',NULL);
INSERT INTO `respuesta` VALUES('279','25','4','4',NULL);
INSERT INTO `respuesta` VALUES('280','25','5','8',NULL);
INSERT INTO `respuesta` VALUES('281','25','6','12',NULL);
INSERT INTO `respuesta` VALUES('282','25','7','16',NULL);
INSERT INTO `respuesta` VALUES('283','25','8','20',NULL);
INSERT INTO `respuesta` VALUES('284','25','9','24',NULL);
INSERT INTO `respuesta` VALUES('285','26','4','4',NULL);
INSERT INTO `respuesta` VALUES('286','26','5','8',NULL);
INSERT INTO `respuesta` VALUES('287','26','6','12',NULL);
INSERT INTO `respuesta` VALUES('288','26','7','16',NULL);
INSERT INTO `respuesta` VALUES('289','26','8','20',NULL);
INSERT INTO `respuesta` VALUES('290','26','9','24',NULL);
INSERT INTO `respuesta` VALUES('291','27','4','4',NULL);
INSERT INTO `respuesta` VALUES('292','27','5','8',NULL);
INSERT INTO `respuesta` VALUES('293','27','6','12',NULL);
INSERT INTO `respuesta` VALUES('294','27','7','16',NULL);
INSERT INTO `respuesta` VALUES('295','27','8','20',NULL);
INSERT INTO `respuesta` VALUES('296','27','9','24',NULL);
INSERT INTO `respuesta` VALUES('297','28','4','4',NULL);
INSERT INTO `respuesta` VALUES('298','28','5','8',NULL);
INSERT INTO `respuesta` VALUES('299','28','6','12',NULL);
INSERT INTO `respuesta` VALUES('300','28','7','16',NULL);
INSERT INTO `respuesta` VALUES('301','28','8','20',NULL);
INSERT INTO `respuesta` VALUES('302','28','9','24',NULL);
INSERT INTO `respuesta` VALUES('303','29','4','4',NULL);
INSERT INTO `respuesta` VALUES('304','29','5','8',NULL);
INSERT INTO `respuesta` VALUES('305','29','6','12',NULL);
INSERT INTO `respuesta` VALUES('306','29','7','16',NULL);
INSERT INTO `respuesta` VALUES('307','29','8','20',NULL);
INSERT INTO `respuesta` VALUES('308','29','9','24',NULL);
INSERT INTO `respuesta` VALUES('309','30','4','4',NULL);
INSERT INTO `respuesta` VALUES('310','30','5','8',NULL);
INSERT INTO `respuesta` VALUES('311','30','6','12',NULL);
INSERT INTO `respuesta` VALUES('312','30','7','16',NULL);
INSERT INTO `respuesta` VALUES('313','30','8','20',NULL);
INSERT INTO `respuesta` VALUES('314','30','9','24',NULL);
INSERT INTO `respuesta` VALUES('315','31','4','4',NULL);
INSERT INTO `respuesta` VALUES('316','31','5','8',NULL);
INSERT INTO `respuesta` VALUES('317','31','6','12',NULL);
INSERT INTO `respuesta` VALUES('318','31','7','16',NULL);
INSERT INTO `respuesta` VALUES('319','31','8','20',NULL);
INSERT INTO `respuesta` VALUES('320','31','9','24',NULL);
INSERT INTO `respuesta` VALUES('321','32','4','4',NULL);
INSERT INTO `respuesta` VALUES('322','32','5','8',NULL);
INSERT INTO `respuesta` VALUES('323','32','6','12',NULL);
INSERT INTO `respuesta` VALUES('324','32','7','16',NULL);
INSERT INTO `respuesta` VALUES('325','32','8','20',NULL);
INSERT INTO `respuesta` VALUES('326','32','9','24',NULL);
INSERT INTO `respuesta` VALUES('327','2','10','26',NULL);
INSERT INTO `respuesta` VALUES('328','2','11','30',NULL);
INSERT INTO `respuesta` VALUES('329','3','10','26',NULL);
INSERT INTO `respuesta` VALUES('330','3','11','30',NULL);
INSERT INTO `respuesta` VALUES('331','4','10','26',NULL);
INSERT INTO `respuesta` VALUES('332','4','11','30',NULL);
INSERT INTO `respuesta` VALUES('333','5','10','26',NULL);
INSERT INTO `respuesta` VALUES('334','5','11','30',NULL);
INSERT INTO `respuesta` VALUES('335','6','10','26',NULL);
INSERT INTO `respuesta` VALUES('336','6','11','30',NULL);
INSERT INTO `respuesta` VALUES('337','7','10','26',NULL);
INSERT INTO `respuesta` VALUES('338','7','11','30',NULL);
INSERT INTO `respuesta` VALUES('339','8','10','26',NULL);
INSERT INTO `respuesta` VALUES('340','8','11','30',NULL);
INSERT INTO `respuesta` VALUES('341','9','10','26',NULL);
INSERT INTO `respuesta` VALUES('342','9','11','30',NULL);
INSERT INTO `respuesta` VALUES('343','10','10','26',NULL);
INSERT INTO `respuesta` VALUES('344','10','11','30',NULL);
INSERT INTO `respuesta` VALUES('345','11','10','26',NULL);
INSERT INTO `respuesta` VALUES('346','11','11','30',NULL);
INSERT INTO `respuesta` VALUES('347','12','10','26',NULL);
INSERT INTO `respuesta` VALUES('348','12','11','30',NULL);
INSERT INTO `respuesta` VALUES('349','13','10','26',NULL);
INSERT INTO `respuesta` VALUES('350','13','11','30',NULL);
INSERT INTO `respuesta` VALUES('351','14','10','26',NULL);
INSERT INTO `respuesta` VALUES('352','14','11','30',NULL);
INSERT INTO `respuesta` VALUES('353','15','10','26',NULL);
INSERT INTO `respuesta` VALUES('354','15','11','30',NULL);
INSERT INTO `respuesta` VALUES('355','16','10','26',NULL);
INSERT INTO `respuesta` VALUES('356','16','11','30',NULL);
INSERT INTO `respuesta` VALUES('357','17','10','26',NULL);
INSERT INTO `respuesta` VALUES('358','17','11','30',NULL);
INSERT INTO `respuesta` VALUES('359','18','10','26',NULL);
INSERT INTO `respuesta` VALUES('360','18','11','30',NULL);
INSERT INTO `respuesta` VALUES('361','19','10','26',NULL);
INSERT INTO `respuesta` VALUES('362','19','11','30',NULL);
INSERT INTO `respuesta` VALUES('363','20','10','26',NULL);
INSERT INTO `respuesta` VALUES('364','20','11','30',NULL);
INSERT INTO `respuesta` VALUES('365','21','10','26',NULL);
INSERT INTO `respuesta` VALUES('366','21','11','30',NULL);
INSERT INTO `respuesta` VALUES('367','22','10','26',NULL);
INSERT INTO `respuesta` VALUES('368','22','11','30',NULL);
INSERT INTO `respuesta` VALUES('369','23','10','25',NULL);
INSERT INTO `respuesta` VALUES('370','23','13','36',NULL);
INSERT INTO `respuesta` VALUES('371','23','14','198',NULL);
INSERT INTO `respuesta` VALUES('372','23','21','43',NULL);
INSERT INTO `respuesta` VALUES('373','23','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('374','23','23','48',NULL);
INSERT INTO `respuesta` VALUES('375','23','24','52',NULL);
INSERT INTO `respuesta` VALUES('376','23','25','59',NULL);
INSERT INTO `respuesta` VALUES('377','24','10','25',NULL);
INSERT INTO `respuesta` VALUES('378','24','13','36',NULL);
INSERT INTO `respuesta` VALUES('379','24','14','198',NULL);
INSERT INTO `respuesta` VALUES('380','24','21','43',NULL);
INSERT INTO `respuesta` VALUES('381','24','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('382','24','23','48',NULL);
INSERT INTO `respuesta` VALUES('383','24','24','52',NULL);
INSERT INTO `respuesta` VALUES('384','24','25','59',NULL);
INSERT INTO `respuesta` VALUES('385','25','10','25',NULL);
INSERT INTO `respuesta` VALUES('386','25','13','36',NULL);
INSERT INTO `respuesta` VALUES('387','25','14','198',NULL);
INSERT INTO `respuesta` VALUES('388','25','21','43',NULL);
INSERT INTO `respuesta` VALUES('389','25','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('390','25','23','48',NULL);
INSERT INTO `respuesta` VALUES('391','25','24','52',NULL);
INSERT INTO `respuesta` VALUES('392','25','25','59',NULL);
INSERT INTO `respuesta` VALUES('393','26','10','25',NULL);
INSERT INTO `respuesta` VALUES('394','26','13','36',NULL);
INSERT INTO `respuesta` VALUES('395','26','14','198',NULL);
INSERT INTO `respuesta` VALUES('396','26','21','43',NULL);
INSERT INTO `respuesta` VALUES('397','26','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('398','26','23','48',NULL);
INSERT INTO `respuesta` VALUES('399','26','24','52',NULL);
INSERT INTO `respuesta` VALUES('400','26','25','59',NULL);
INSERT INTO `respuesta` VALUES('401','27','10','25',NULL);
INSERT INTO `respuesta` VALUES('402','27','13','36',NULL);
INSERT INTO `respuesta` VALUES('403','27','14','198',NULL);
INSERT INTO `respuesta` VALUES('404','27','21','43',NULL);
INSERT INTO `respuesta` VALUES('405','27','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('406','27','23','48',NULL);
INSERT INTO `respuesta` VALUES('407','27','24','52',NULL);
INSERT INTO `respuesta` VALUES('408','27','25','59',NULL);
INSERT INTO `respuesta` VALUES('409','28','10','25',NULL);
INSERT INTO `respuesta` VALUES('410','28','13','36',NULL);
INSERT INTO `respuesta` VALUES('411','28','14','198',NULL);
INSERT INTO `respuesta` VALUES('412','28','21','43',NULL);
INSERT INTO `respuesta` VALUES('413','28','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('414','28','23','48',NULL);
INSERT INTO `respuesta` VALUES('415','28','24','52',NULL);
INSERT INTO `respuesta` VALUES('416','28','25','59',NULL);
INSERT INTO `respuesta` VALUES('417','29','10','25',NULL);
INSERT INTO `respuesta` VALUES('418','29','13','36',NULL);
INSERT INTO `respuesta` VALUES('419','29','14','198',NULL);
INSERT INTO `respuesta` VALUES('420','29','21','43',NULL);
INSERT INTO `respuesta` VALUES('421','29','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('422','29','23','48',NULL);
INSERT INTO `respuesta` VALUES('423','29','24','52',NULL);
INSERT INTO `respuesta` VALUES('424','29','25','59',NULL);
INSERT INTO `respuesta` VALUES('425','30','10','25',NULL);
INSERT INTO `respuesta` VALUES('426','30','13','36',NULL);
INSERT INTO `respuesta` VALUES('427','30','14','198',NULL);
INSERT INTO `respuesta` VALUES('428','30','21','43',NULL);
INSERT INTO `respuesta` VALUES('429','30','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('430','30','23','48',NULL);
INSERT INTO `respuesta` VALUES('431','30','24','52',NULL);
INSERT INTO `respuesta` VALUES('432','30','25','59',NULL);
INSERT INTO `respuesta` VALUES('433','31','10','25',NULL);
INSERT INTO `respuesta` VALUES('434','31','13','36',NULL);
INSERT INTO `respuesta` VALUES('435','31','14','198',NULL);
INSERT INTO `respuesta` VALUES('436','31','21','43',NULL);
INSERT INTO `respuesta` VALUES('437','31','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('438','31','23','48',NULL);
INSERT INTO `respuesta` VALUES('439','31','24','52',NULL);
INSERT INTO `respuesta` VALUES('440','31','25','59',NULL);
INSERT INTO `respuesta` VALUES('441','32','10','25',NULL);
INSERT INTO `respuesta` VALUES('442','32','13','36',NULL);
INSERT INTO `respuesta` VALUES('443','32','14','198',NULL);
INSERT INTO `respuesta` VALUES('444','32','21','43',NULL);
INSERT INTO `respuesta` VALUES('445','32','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('446','32','23','48',NULL);
INSERT INTO `respuesta` VALUES('447','32','24','52',NULL);
INSERT INTO `respuesta` VALUES('448','32','25','59',NULL);
INSERT INTO `respuesta` VALUES('449','33','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('450','33','2',NULL,'0');
INSERT INTO `respuesta` VALUES('451','33','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('452','34','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('453','34','2',NULL,'0');
INSERT INTO `respuesta` VALUES('454','34','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('455','35','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('456','35','2',NULL,'0');
INSERT INTO `respuesta` VALUES('457','35','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('458','36','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('459','36','2',NULL,'0');
INSERT INTO `respuesta` VALUES('460','36','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('461','37','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('462','37','2',NULL,'0');
INSERT INTO `respuesta` VALUES('463','37','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('464','38','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('465','38','2',NULL,'0');
INSERT INTO `respuesta` VALUES('466','38','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('467','39','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('468','39','2',NULL,'0');
INSERT INTO `respuesta` VALUES('469','39','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('470','40','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('471','40','2',NULL,'0');
INSERT INTO `respuesta` VALUES('472','40','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('473','41','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('474','41','2',NULL,'0');
INSERT INTO `respuesta` VALUES('475','41','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('476','42','1',NULL,'0.1');
INSERT INTO `respuesta` VALUES('477','42','2',NULL,'0');
INSERT INTO `respuesta` VALUES('478','42','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('479','43','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('480','43','2',NULL,'0');
INSERT INTO `respuesta` VALUES('481','43','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('482','44','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('483','44','2',NULL,'0');
INSERT INTO `respuesta` VALUES('484','44','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('485','45','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('486','45','2',NULL,'0');
INSERT INTO `respuesta` VALUES('487','45','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('488','46','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('489','46','2',NULL,'0');
INSERT INTO `respuesta` VALUES('490','46','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('491','47','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('492','47','2',NULL,'0');
INSERT INTO `respuesta` VALUES('493','47','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('494','48','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('495','48','2',NULL,'0');
INSERT INTO `respuesta` VALUES('496','48','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('497','49','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('498','49','2',NULL,'0');
INSERT INTO `respuesta` VALUES('499','49','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('500','50','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('501','50','2',NULL,'0');
INSERT INTO `respuesta` VALUES('502','50','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('503','51','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('504','51','2',NULL,'0');
INSERT INTO `respuesta` VALUES('505','51','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('506','52','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('507','52','2',NULL,'0');
INSERT INTO `respuesta` VALUES('508','52','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('509','53','1',NULL,'0.5');
INSERT INTO `respuesta` VALUES('510','53','2',NULL,'0');
INSERT INTO `respuesta` VALUES('511','53','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('512','54','1',NULL,'1');
INSERT INTO `respuesta` VALUES('513','54','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('514','54','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('515','55','1',NULL,'1');
INSERT INTO `respuesta` VALUES('516','55','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('517','55','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('518','56','1',NULL,'1');
INSERT INTO `respuesta` VALUES('519','56','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('520','56','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('521','57','1',NULL,'1');
INSERT INTO `respuesta` VALUES('522','57','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('523','57','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('524','58','1',NULL,'1');
INSERT INTO `respuesta` VALUES('525','58','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('526','58','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('527','59','1',NULL,'1');
INSERT INTO `respuesta` VALUES('528','59','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('529','59','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('530','60','1',NULL,'1');
INSERT INTO `respuesta` VALUES('531','60','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('532','60','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('533','61','1',NULL,'1');
INSERT INTO `respuesta` VALUES('534','61','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('535','61','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('536','62','1',NULL,'1');
INSERT INTO `respuesta` VALUES('537','62','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('538','62','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('539','63','1',NULL,'1');
INSERT INTO `respuesta` VALUES('540','63','2',NULL,'0.1');
INSERT INTO `respuesta` VALUES('541','63','3',NULL,'Microsoft Office');
INSERT INTO `respuesta` VALUES('542','33','4','1',NULL);
INSERT INTO `respuesta` VALUES('543','33','5','5',NULL);
INSERT INTO `respuesta` VALUES('544','33','6','10',NULL);
INSERT INTO `respuesta` VALUES('545','33','7','14',NULL);
INSERT INTO `respuesta` VALUES('546','33','8','18',NULL);
INSERT INTO `respuesta` VALUES('547','33','9','22',NULL);
INSERT INTO `respuesta` VALUES('548','34','4','1',NULL);
INSERT INTO `respuesta` VALUES('549','34','5','5',NULL);
INSERT INTO `respuesta` VALUES('550','34','6','10',NULL);
INSERT INTO `respuesta` VALUES('551','34','7','14',NULL);
INSERT INTO `respuesta` VALUES('552','34','8','18',NULL);
INSERT INTO `respuesta` VALUES('553','34','9','22',NULL);
INSERT INTO `respuesta` VALUES('554','35','4','1',NULL);
INSERT INTO `respuesta` VALUES('555','35','5','5',NULL);
INSERT INTO `respuesta` VALUES('556','35','6','10',NULL);
INSERT INTO `respuesta` VALUES('557','35','7','14',NULL);
INSERT INTO `respuesta` VALUES('558','35','8','18',NULL);
INSERT INTO `respuesta` VALUES('559','35','9','22',NULL);
INSERT INTO `respuesta` VALUES('560','36','4','1',NULL);
INSERT INTO `respuesta` VALUES('561','36','5','5',NULL);
INSERT INTO `respuesta` VALUES('562','36','6','10',NULL);
INSERT INTO `respuesta` VALUES('563','36','7','14',NULL);
INSERT INTO `respuesta` VALUES('564','36','8','18',NULL);
INSERT INTO `respuesta` VALUES('565','36','9','22',NULL);
INSERT INTO `respuesta` VALUES('566','37','4','1',NULL);
INSERT INTO `respuesta` VALUES('567','37','5','5',NULL);
INSERT INTO `respuesta` VALUES('568','37','6','10',NULL);
INSERT INTO `respuesta` VALUES('569','37','7','14',NULL);
INSERT INTO `respuesta` VALUES('570','37','8','18',NULL);
INSERT INTO `respuesta` VALUES('571','37','9','22',NULL);
INSERT INTO `respuesta` VALUES('572','38','4','1',NULL);
INSERT INTO `respuesta` VALUES('573','38','5','5',NULL);
INSERT INTO `respuesta` VALUES('574','38','6','10',NULL);
INSERT INTO `respuesta` VALUES('575','38','7','14',NULL);
INSERT INTO `respuesta` VALUES('576','38','8','18',NULL);
INSERT INTO `respuesta` VALUES('577','38','9','22',NULL);
INSERT INTO `respuesta` VALUES('578','39','4','1',NULL);
INSERT INTO `respuesta` VALUES('579','39','5','5',NULL);
INSERT INTO `respuesta` VALUES('580','39','6','10',NULL);
INSERT INTO `respuesta` VALUES('581','39','7','14',NULL);
INSERT INTO `respuesta` VALUES('582','39','8','18',NULL);
INSERT INTO `respuesta` VALUES('583','39','9','22',NULL);
INSERT INTO `respuesta` VALUES('584','40','4','1',NULL);
INSERT INTO `respuesta` VALUES('585','40','5','5',NULL);
INSERT INTO `respuesta` VALUES('586','40','6','10',NULL);
INSERT INTO `respuesta` VALUES('587','40','7','14',NULL);
INSERT INTO `respuesta` VALUES('588','40','8','18',NULL);
INSERT INTO `respuesta` VALUES('589','40','9','22',NULL);
INSERT INTO `respuesta` VALUES('590','41','4','1',NULL);
INSERT INTO `respuesta` VALUES('591','41','5','5',NULL);
INSERT INTO `respuesta` VALUES('592','41','6','10',NULL);
INSERT INTO `respuesta` VALUES('593','41','7','14',NULL);
INSERT INTO `respuesta` VALUES('594','41','8','18',NULL);
INSERT INTO `respuesta` VALUES('595','41','9','22',NULL);
INSERT INTO `respuesta` VALUES('596','42','4','1',NULL);
INSERT INTO `respuesta` VALUES('597','42','5','5',NULL);
INSERT INTO `respuesta` VALUES('598','42','6','10',NULL);
INSERT INTO `respuesta` VALUES('599','42','7','14',NULL);
INSERT INTO `respuesta` VALUES('600','42','8','18',NULL);
INSERT INTO `respuesta` VALUES('601','42','9','22',NULL);
INSERT INTO `respuesta` VALUES('602','43','4','3',NULL);
INSERT INTO `respuesta` VALUES('603','43','5','7',NULL);
INSERT INTO `respuesta` VALUES('604','43','6','11',NULL);
INSERT INTO `respuesta` VALUES('605','43','7','15',NULL);
INSERT INTO `respuesta` VALUES('606','43','8','19',NULL);
INSERT INTO `respuesta` VALUES('607','43','9','23',NULL);
INSERT INTO `respuesta` VALUES('608','44','4','3',NULL);
INSERT INTO `respuesta` VALUES('609','44','5','7',NULL);
INSERT INTO `respuesta` VALUES('610','44','6','11',NULL);
INSERT INTO `respuesta` VALUES('611','44','7','15',NULL);
INSERT INTO `respuesta` VALUES('612','44','8','19',NULL);
INSERT INTO `respuesta` VALUES('613','44','9','23',NULL);
INSERT INTO `respuesta` VALUES('614','45','4','3',NULL);
INSERT INTO `respuesta` VALUES('615','45','5','7',NULL);
INSERT INTO `respuesta` VALUES('616','45','6','11',NULL);
INSERT INTO `respuesta` VALUES('617','45','7','15',NULL);
INSERT INTO `respuesta` VALUES('618','45','8','19',NULL);
INSERT INTO `respuesta` VALUES('619','45','9','23',NULL);
INSERT INTO `respuesta` VALUES('620','46','4','3',NULL);
INSERT INTO `respuesta` VALUES('621','46','5','7',NULL);
INSERT INTO `respuesta` VALUES('622','46','6','11',NULL);
INSERT INTO `respuesta` VALUES('623','46','7','15',NULL);
INSERT INTO `respuesta` VALUES('624','46','8','19',NULL);
INSERT INTO `respuesta` VALUES('625','46','9','23',NULL);
INSERT INTO `respuesta` VALUES('626','47','4','3',NULL);
INSERT INTO `respuesta` VALUES('627','47','5','7',NULL);
INSERT INTO `respuesta` VALUES('628','47','6','11',NULL);
INSERT INTO `respuesta` VALUES('629','47','7','15',NULL);
INSERT INTO `respuesta` VALUES('630','47','8','19',NULL);
INSERT INTO `respuesta` VALUES('631','47','9','23',NULL);
INSERT INTO `respuesta` VALUES('632','48','4','3',NULL);
INSERT INTO `respuesta` VALUES('633','48','5','7',NULL);
INSERT INTO `respuesta` VALUES('634','48','6','11',NULL);
INSERT INTO `respuesta` VALUES('635','48','7','15',NULL);
INSERT INTO `respuesta` VALUES('636','48','8','19',NULL);
INSERT INTO `respuesta` VALUES('637','48','9','23',NULL);
INSERT INTO `respuesta` VALUES('638','49','4','3',NULL);
INSERT INTO `respuesta` VALUES('639','49','5','7',NULL);
INSERT INTO `respuesta` VALUES('640','49','6','11',NULL);
INSERT INTO `respuesta` VALUES('641','49','7','15',NULL);
INSERT INTO `respuesta` VALUES('642','49','8','19',NULL);
INSERT INTO `respuesta` VALUES('643','49','9','23',NULL);
INSERT INTO `respuesta` VALUES('644','50','4','3',NULL);
INSERT INTO `respuesta` VALUES('645','50','5','7',NULL);
INSERT INTO `respuesta` VALUES('646','50','6','11',NULL);
INSERT INTO `respuesta` VALUES('647','50','7','15',NULL);
INSERT INTO `respuesta` VALUES('648','50','8','19',NULL);
INSERT INTO `respuesta` VALUES('649','50','9','23',NULL);
INSERT INTO `respuesta` VALUES('650','51','4','3',NULL);
INSERT INTO `respuesta` VALUES('651','51','5','7',NULL);
INSERT INTO `respuesta` VALUES('652','51','6','11',NULL);
INSERT INTO `respuesta` VALUES('653','51','7','15',NULL);
INSERT INTO `respuesta` VALUES('654','51','8','19',NULL);
INSERT INTO `respuesta` VALUES('655','51','9','23',NULL);
INSERT INTO `respuesta` VALUES('656','52','4','3',NULL);
INSERT INTO `respuesta` VALUES('657','52','5','7',NULL);
INSERT INTO `respuesta` VALUES('658','52','6','11',NULL);
INSERT INTO `respuesta` VALUES('659','52','7','15',NULL);
INSERT INTO `respuesta` VALUES('660','52','8','19',NULL);
INSERT INTO `respuesta` VALUES('661','52','9','23',NULL);
INSERT INTO `respuesta` VALUES('662','53','4','3',NULL);
INSERT INTO `respuesta` VALUES('663','53','5','7',NULL);
INSERT INTO `respuesta` VALUES('664','53','6','11',NULL);
INSERT INTO `respuesta` VALUES('665','53','7','15',NULL);
INSERT INTO `respuesta` VALUES('666','53','8','19',NULL);
INSERT INTO `respuesta` VALUES('667','53','9','23',NULL);
INSERT INTO `respuesta` VALUES('668','54','4','4',NULL);
INSERT INTO `respuesta` VALUES('669','54','5','8',NULL);
INSERT INTO `respuesta` VALUES('670','54','6','12',NULL);
INSERT INTO `respuesta` VALUES('671','54','7','16',NULL);
INSERT INTO `respuesta` VALUES('672','54','8','20',NULL);
INSERT INTO `respuesta` VALUES('673','54','9','24',NULL);
INSERT INTO `respuesta` VALUES('674','55','4','4',NULL);
INSERT INTO `respuesta` VALUES('675','55','5','8',NULL);
INSERT INTO `respuesta` VALUES('676','55','6','12',NULL);
INSERT INTO `respuesta` VALUES('677','55','7','16',NULL);
INSERT INTO `respuesta` VALUES('678','55','8','20',NULL);
INSERT INTO `respuesta` VALUES('679','55','9','24',NULL);
INSERT INTO `respuesta` VALUES('680','56','4','4',NULL);
INSERT INTO `respuesta` VALUES('681','56','5','8',NULL);
INSERT INTO `respuesta` VALUES('682','56','6','12',NULL);
INSERT INTO `respuesta` VALUES('683','56','7','16',NULL);
INSERT INTO `respuesta` VALUES('684','56','8','20',NULL);
INSERT INTO `respuesta` VALUES('685','56','9','24',NULL);
INSERT INTO `respuesta` VALUES('686','57','4','4',NULL);
INSERT INTO `respuesta` VALUES('687','57','5','8',NULL);
INSERT INTO `respuesta` VALUES('688','57','6','12',NULL);
INSERT INTO `respuesta` VALUES('689','57','7','16',NULL);
INSERT INTO `respuesta` VALUES('690','57','8','20',NULL);
INSERT INTO `respuesta` VALUES('691','57','9','24',NULL);
INSERT INTO `respuesta` VALUES('692','58','4','4',NULL);
INSERT INTO `respuesta` VALUES('693','58','5','8',NULL);
INSERT INTO `respuesta` VALUES('694','58','6','12',NULL);
INSERT INTO `respuesta` VALUES('695','58','7','16',NULL);
INSERT INTO `respuesta` VALUES('696','58','8','20',NULL);
INSERT INTO `respuesta` VALUES('697','58','9','24',NULL);
INSERT INTO `respuesta` VALUES('698','59','4','4',NULL);
INSERT INTO `respuesta` VALUES('699','59','5','8',NULL);
INSERT INTO `respuesta` VALUES('700','59','6','12',NULL);
INSERT INTO `respuesta` VALUES('701','59','7','16',NULL);
INSERT INTO `respuesta` VALUES('702','59','8','20',NULL);
INSERT INTO `respuesta` VALUES('703','59','9','24',NULL);
INSERT INTO `respuesta` VALUES('704','60','4','4',NULL);
INSERT INTO `respuesta` VALUES('705','60','5','8',NULL);
INSERT INTO `respuesta` VALUES('706','60','6','12',NULL);
INSERT INTO `respuesta` VALUES('707','60','7','16',NULL);
INSERT INTO `respuesta` VALUES('708','60','8','20',NULL);
INSERT INTO `respuesta` VALUES('709','60','9','24',NULL);
INSERT INTO `respuesta` VALUES('710','61','4','4',NULL);
INSERT INTO `respuesta` VALUES('711','61','5','8',NULL);
INSERT INTO `respuesta` VALUES('712','61','6','12',NULL);
INSERT INTO `respuesta` VALUES('713','61','7','16',NULL);
INSERT INTO `respuesta` VALUES('714','61','8','20',NULL);
INSERT INTO `respuesta` VALUES('715','61','9','24',NULL);
INSERT INTO `respuesta` VALUES('716','62','4','4',NULL);
INSERT INTO `respuesta` VALUES('717','62','5','8',NULL);
INSERT INTO `respuesta` VALUES('718','62','6','12',NULL);
INSERT INTO `respuesta` VALUES('719','62','7','16',NULL);
INSERT INTO `respuesta` VALUES('720','62','8','20',NULL);
INSERT INTO `respuesta` VALUES('721','62','9','24',NULL);
INSERT INTO `respuesta` VALUES('722','63','4','4',NULL);
INSERT INTO `respuesta` VALUES('723','63','5','8',NULL);
INSERT INTO `respuesta` VALUES('724','63','6','12',NULL);
INSERT INTO `respuesta` VALUES('725','63','7','16',NULL);
INSERT INTO `respuesta` VALUES('726','63','8','20',NULL);
INSERT INTO `respuesta` VALUES('727','63','9','24',NULL);
INSERT INTO `respuesta` VALUES('728','33','10','26',NULL);
INSERT INTO `respuesta` VALUES('729','33','11','30',NULL);
INSERT INTO `respuesta` VALUES('730','34','10','26',NULL);
INSERT INTO `respuesta` VALUES('731','34','11','30',NULL);
INSERT INTO `respuesta` VALUES('732','35','10','26',NULL);
INSERT INTO `respuesta` VALUES('733','35','11','30',NULL);
INSERT INTO `respuesta` VALUES('734','36','10','26',NULL);
INSERT INTO `respuesta` VALUES('735','36','11','30',NULL);
INSERT INTO `respuesta` VALUES('736','37','10','26',NULL);
INSERT INTO `respuesta` VALUES('737','37','11','30',NULL);
INSERT INTO `respuesta` VALUES('738','38','10','26',NULL);
INSERT INTO `respuesta` VALUES('739','38','11','30',NULL);
INSERT INTO `respuesta` VALUES('740','39','10','26',NULL);
INSERT INTO `respuesta` VALUES('741','39','11','30',NULL);
INSERT INTO `respuesta` VALUES('742','40','10','26',NULL);
INSERT INTO `respuesta` VALUES('743','40','11','30',NULL);
INSERT INTO `respuesta` VALUES('744','41','10','26',NULL);
INSERT INTO `respuesta` VALUES('745','41','11','30',NULL);
INSERT INTO `respuesta` VALUES('746','42','10','26',NULL);
INSERT INTO `respuesta` VALUES('747','42','11','30',NULL);
INSERT INTO `respuesta` VALUES('748','43','10','26',NULL);
INSERT INTO `respuesta` VALUES('749','43','11','30',NULL);
INSERT INTO `respuesta` VALUES('750','44','10','26',NULL);
INSERT INTO `respuesta` VALUES('751','44','11','30',NULL);
INSERT INTO `respuesta` VALUES('752','45','10','26',NULL);
INSERT INTO `respuesta` VALUES('753','45','11','30',NULL);
INSERT INTO `respuesta` VALUES('754','46','10','26',NULL);
INSERT INTO `respuesta` VALUES('755','46','11','30',NULL);
INSERT INTO `respuesta` VALUES('756','47','10','26',NULL);
INSERT INTO `respuesta` VALUES('757','47','11','30',NULL);
INSERT INTO `respuesta` VALUES('758','48','10','26',NULL);
INSERT INTO `respuesta` VALUES('759','48','11','30',NULL);
INSERT INTO `respuesta` VALUES('760','49','10','26',NULL);
INSERT INTO `respuesta` VALUES('761','49','11','30',NULL);
INSERT INTO `respuesta` VALUES('762','50','10','26',NULL);
INSERT INTO `respuesta` VALUES('763','50','11','30',NULL);
INSERT INTO `respuesta` VALUES('764','51','10','26',NULL);
INSERT INTO `respuesta` VALUES('765','51','11','30',NULL);
INSERT INTO `respuesta` VALUES('766','52','10','26',NULL);
INSERT INTO `respuesta` VALUES('767','52','11','30',NULL);
INSERT INTO `respuesta` VALUES('768','53','10','26',NULL);
INSERT INTO `respuesta` VALUES('769','53','11','30',NULL);
INSERT INTO `respuesta` VALUES('770','54','10','25',NULL);
INSERT INTO `respuesta` VALUES('771','54','13','36',NULL);
INSERT INTO `respuesta` VALUES('772','54','14','198',NULL);
INSERT INTO `respuesta` VALUES('773','54','21','43',NULL);
INSERT INTO `respuesta` VALUES('774','54','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('775','54','23','48',NULL);
INSERT INTO `respuesta` VALUES('776','54','24','52',NULL);
INSERT INTO `respuesta` VALUES('777','54','25','59',NULL);
INSERT INTO `respuesta` VALUES('778','55','10','25',NULL);
INSERT INTO `respuesta` VALUES('779','55','13','36',NULL);
INSERT INTO `respuesta` VALUES('780','55','14','198',NULL);
INSERT INTO `respuesta` VALUES('781','55','21','43',NULL);
INSERT INTO `respuesta` VALUES('782','55','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('783','55','23','48',NULL);
INSERT INTO `respuesta` VALUES('784','55','24','52',NULL);
INSERT INTO `respuesta` VALUES('785','55','25','59',NULL);
INSERT INTO `respuesta` VALUES('786','56','10','25',NULL);
INSERT INTO `respuesta` VALUES('787','56','13','36',NULL);
INSERT INTO `respuesta` VALUES('788','56','14','198',NULL);
INSERT INTO `respuesta` VALUES('789','56','21','43',NULL);
INSERT INTO `respuesta` VALUES('790','56','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('791','56','23','48',NULL);
INSERT INTO `respuesta` VALUES('792','56','24','52',NULL);
INSERT INTO `respuesta` VALUES('793','56','25','59',NULL);
INSERT INTO `respuesta` VALUES('794','57','10','25',NULL);
INSERT INTO `respuesta` VALUES('795','57','13','36',NULL);
INSERT INTO `respuesta` VALUES('796','57','14','198',NULL);
INSERT INTO `respuesta` VALUES('797','57','21','43',NULL);
INSERT INTO `respuesta` VALUES('798','57','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('799','57','23','48',NULL);
INSERT INTO `respuesta` VALUES('800','57','24','52',NULL);
INSERT INTO `respuesta` VALUES('801','57','25','59',NULL);
INSERT INTO `respuesta` VALUES('802','58','10','25',NULL);
INSERT INTO `respuesta` VALUES('803','58','13','36',NULL);
INSERT INTO `respuesta` VALUES('804','58','14','198',NULL);
INSERT INTO `respuesta` VALUES('805','58','21','43',NULL);
INSERT INTO `respuesta` VALUES('806','58','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('807','58','23','48',NULL);
INSERT INTO `respuesta` VALUES('808','58','24','52',NULL);
INSERT INTO `respuesta` VALUES('809','58','25','59',NULL);
INSERT INTO `respuesta` VALUES('810','59','10','25',NULL);
INSERT INTO `respuesta` VALUES('811','59','13','36',NULL);
INSERT INTO `respuesta` VALUES('812','59','14','198',NULL);
INSERT INTO `respuesta` VALUES('813','59','21','43',NULL);
INSERT INTO `respuesta` VALUES('814','59','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('815','59','23','48',NULL);
INSERT INTO `respuesta` VALUES('816','59','24','52',NULL);
INSERT INTO `respuesta` VALUES('817','59','25','59',NULL);
INSERT INTO `respuesta` VALUES('818','60','10','25',NULL);
INSERT INTO `respuesta` VALUES('819','60','13','36',NULL);
INSERT INTO `respuesta` VALUES('820','60','14','198',NULL);
INSERT INTO `respuesta` VALUES('821','60','21','43',NULL);
INSERT INTO `respuesta` VALUES('822','60','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('823','60','23','48',NULL);
INSERT INTO `respuesta` VALUES('824','60','24','52',NULL);
INSERT INTO `respuesta` VALUES('825','60','25','59',NULL);
INSERT INTO `respuesta` VALUES('826','61','10','25',NULL);
INSERT INTO `respuesta` VALUES('827','61','13','36',NULL);
INSERT INTO `respuesta` VALUES('828','61','14','198',NULL);
INSERT INTO `respuesta` VALUES('829','61','21','43',NULL);
INSERT INTO `respuesta` VALUES('830','61','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('831','61','23','48',NULL);
INSERT INTO `respuesta` VALUES('832','61','24','52',NULL);
INSERT INTO `respuesta` VALUES('833','61','25','59',NULL);
INSERT INTO `respuesta` VALUES('834','62','10','25',NULL);
INSERT INTO `respuesta` VALUES('835','62','13','36',NULL);
INSERT INTO `respuesta` VALUES('836','62','14','198',NULL);
INSERT INTO `respuesta` VALUES('837','62','21','43',NULL);
INSERT INTO `respuesta` VALUES('838','62','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('839','62','23','48',NULL);
INSERT INTO `respuesta` VALUES('840','62','24','52',NULL);
INSERT INTO `respuesta` VALUES('841','62','25','59',NULL);
INSERT INTO `respuesta` VALUES('842','63','10','25',NULL);
INSERT INTO `respuesta` VALUES('843','63','13','36',NULL);
INSERT INTO `respuesta` VALUES('844','63','14','198',NULL);
INSERT INTO `respuesta` VALUES('845','63','21','43',NULL);
INSERT INTO `respuesta` VALUES('846','63','22',NULL,'2023');
INSERT INTO `respuesta` VALUES('847','63','23','48',NULL);
INSERT INTO `respuesta` VALUES('848','63','24','52',NULL);
INSERT INTO `respuesta` VALUES('849','63','25','59',NULL);


DROP TABLE IF EXISTS `seccion`;
CREATE TABLE `seccion` (
  `ID_SECCION` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE` varchar(100) DEFAULT NULL,
  `ORDEN` int(11) DEFAULT NULL,
  `PARA_CARRERA` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID_SECCION`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `seccion` VALUES('1','I. Perfil del Egresado','1',NULL);
INSERT INTO `seccion` VALUES('2','II.Recursos para el Aprendizaje','2',NULL);
INSERT INTO `seccion` VALUES('3','III. Ubicación Laboral','3',NULL);
INSERT INTO `seccion` VALUES('4','IV. Desempeño Profesional','4',NULL);
INSERT INTO `seccion` VALUES('5','V. Expectativas de Desarrollo','5',NULL);
INSERT INTO `seccion` VALUES('6','VI. Participación Social','6',NULL);
INSERT INTO `seccion` VALUES('7','VII. Comentarios y Sugerencias','7',NULL);
INSERT INTO `seccion` VALUES('8','Datos Generales','10','quimica');
INSERT INTO `seccion` VALUES('9','Situación Laboral','11','quimica');
INSERT INTO `seccion` VALUES('10','Plan de Estudios','12','quimica');
INSERT INTO `seccion` VALUES('11','Institución','13','quimica');
INSERT INTO `seccion` VALUES('12','Desempeño Laboral','14','quimica');


DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `RFC` varchar(13) NOT NULL,
  `NOMBRE` varchar(100) NOT NULL,
  `APELLIDO_PATERNO` varchar(50) DEFAULT NULL,
  `APELLIDO_MATERNO` varchar(50) DEFAULT NULL,
  `EMAIL` varchar(100) NOT NULL,
  `CONTRASENA` varchar(255) NOT NULL,
  `ROL` enum('DBA','Administrador','Jefe Departamento','Jefe Vinculación') NOT NULL,
  `CARRERA` enum('Licenciatura en Administración','Ingeniería Bioquímica','Ingeniería Eléctrica','Ingeniería Electrónica','Ingeniería Industrial','Ingeniería Mecatrónica','Ingeniería Mecánica','Ingeniería en Sistemas Computacionales','Ingeniería Química','Ingeniería en Energías Renovables','Ingeniería en Gestión Empresarial') DEFAULT NULL,
  PRIMARY KEY (`RFC`),
  UNIQUE KEY `EMAIL` (`EMAIL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuario` VALUES('GOTS040611BH5','SAMUEL ANTONIO','GONZALEZ','TELLO','samueltello11@gmail.comm','0712','Administrador',NULL);
INSERT INTO `usuario` VALUES('PALM021018K21','DAYAN','PAZARON','LEYVA','dayan2838@gmail.com','0712','DBA',NULL);
INSERT INTO `usuario` VALUES('USCZ020805J36','ZULEYMA','USCANGA','CRUZ','valeriagarco04@gmail.com','0712','Jefe Departamento','Ingeniería Química');


