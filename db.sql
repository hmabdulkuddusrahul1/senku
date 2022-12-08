CREATE TABLE `bins` (
  `bin` int(7) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `level` varchar(100) DEFAULT NULL,
  `bank_name` varchar(200) DEFAULT NULL,
  `bank_site` varchar(200) DEFAULT NULL,
  `bank_phone` varchar(200) DEFAULT NULL,
  `country_name` varchar(300) DEFAULT NULL,
  `ISO2` varchar(200) DEFAULT NULL,
  `ISO3` varchar(200) DEFAULT NULL,
  `currency` varchar(200) DEFAULT NULL,
  `flag` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `bins`
  ADD PRIMARY KEY (`bin`);