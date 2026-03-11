-- 为 products 表中的商品生成型号数据
-- 每个商品生成 1~3 个型号，价格在 1~100 之间随机

-- 先清空现有的 products_attributes 数据（可选，如需保留现有数据请注释掉）
-- TRUNCATE TABLE products_attributes;

-- 插入型号数据
-- ProductID 1: Current Sensor - 3个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(1, 'CS-001-A', 'UPC001A', 'Standard Model', 1, 5.00, 15.99, 2.00, 0.50, 1, 'Standard current sensor model'),
(1, 'CS-001-B', 'UPC001B', 'Pro Model', 2, 8.00, 29.99, 2.50, 0.60, 1, 'Professional grade with higher accuracy'),
(1, 'CS-001-C', 'UPC001C', 'Industrial Model', 3, 12.00, 49.99, 3.00, 0.80, 1, 'Heavy duty industrial version');

-- ProductID 2: Sensor -no.2 - 2个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(2, 'SEN-002-A', 'UPC002A', 'Basic', 1, 3.00, 8.50, 1.50, 0.30, 1, 'Basic sensor module'),
(2, 'SEN-002-B', 'UPC002B', 'Advanced', 2, 6.00, 18.99, 2.00, 0.45, 1, 'Advanced sensor with calibration');

-- ProductID 3: Sensor- No.3 - 3个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(3, 'SEN-003-A', 'UPC003A', 'Mini', 1, 2.50, 6.99, 1.00, 0.20, 1, 'Compact mini size'),
(3, 'SEN-003-B', 'UPC003B', 'Standard', 2, 4.00, 12.50, 1.50, 0.35, 1, 'Standard size sensor'),
(3, 'SEN-003-C', 'UPC003C', 'Extended Range', 3, 7.00, 25.00, 2.00, 0.50, 1, 'Extended measurement range');

-- ProductID 4: Solid State Relays - 2个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(4, 'SSR-004-A', 'UPC004A', '10A Model', 1, 4.00, 11.99, 1.50, 0.40, 1, '10 Amp rating'),
(4, 'SSR-004-B', 'UPC004B', '25A Model', 2, 7.00, 22.50, 2.00, 0.60, 1, '25 Amp high current rating');

-- ProductID 5: Solid State Relay2 - 3个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(5, 'SSR-005-A', 'UPC005A', 'DC Control', 1, 5.00, 14.99, 1.50, 0.35, 1, 'DC input control'),
(5, 'SSR-005-B', 'UPC005B', 'AC Control', 2, 5.50, 15.99, 1.50, 0.35, 1, 'AC input control'),
(5, 'SSR-005-C', 'UPC005C', 'Universal', 3, 8.00, 24.99, 2.00, 0.50, 1, 'Universal AC/DC control');

-- ProductID 6: Mechnical Relays - 2个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(6, 'MR-006-A', 'UPC006A', 'SPDT', 1, 3.00, 9.50, 1.00, 0.25, 1, 'Single Pole Double Throw'),
(6, 'MR-006-B', 'UPC006B', 'DPDT', 2, 4.50, 13.99, 1.50, 0.35, 1, 'Double Pole Double Throw');

-- ProductID 7: Valve-No1 - 3个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(7, 'VAL-007-A', 'UPC007A', '1/4 inch', 1, 6.00, 19.99, 2.50, 0.80, 1, '1/4 inch port size'),
(7, 'VAL-007-B', 'UPC007B', '1/2 inch', 2, 8.00, 27.50, 3.00, 1.00, 1, '1/2 inch port size'),
(7, 'VAL-007-C', 'UPC007C', '3/4 inch', 3, 12.00, 42.00, 3.50, 1.20, 1, '3/4 inch port size');

-- ProductID 8: Valve-No2 - 2个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(8, 'VAL-008-A', 'UPC008A', 'Brass', 1, 7.00, 22.99, 2.50, 0.90, 1, 'Brass construction'),
(8, 'VAL-008-B', 'UPC008B', 'Stainless Steel', 2, 15.00, 45.00, 3.00, 1.10, 1, 'Stainless steel construction');

-- ProductID 9: Valve-No.3 - 3个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(9, 'VAL-009-A', 'UPC009A', '12V DC', 1, 5.00, 16.50, 2.00, 0.70, 1, '12V DC coil'),
(9, 'VAL-009-B', 'UPC009B', '24V DC', 2, 5.50, 17.99, 2.00, 0.70, 1, '24V DC coil'),
(9, 'VAL-009-C', 'UPC009C', '110V AC', 3, 6.00, 19.50, 2.00, 0.75, 1, '110V AC coil');

-- ProductID 10: Plastic enclosure no.1 - 2个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(10, 'ENC-010-A', 'UPC010A', 'Small 100x60mm', 1, 2.00, 5.99, 1.50, 0.20, 1, 'Small size 100x60x35mm'),
(10, 'ENC-010-B', 'UPC010B', 'Large 150x90mm', 2, 3.50, 9.99, 2.00, 0.35, 1, 'Large size 150x90x50mm');

-- ProductID 11: Plastic Enclosures - 3个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(11, 'ENC-011-A', 'UPC011A', 'Type A', 1, 2.50, 7.50, 1.50, 0.25, 1, 'Enclosure Type A'),
(11, 'ENC-011-B', 'UPC011B', 'Type B', 2, 3.00, 8.99, 1.80, 0.30, 1, 'Enclosure Type B'),
(11, 'ENC-011-C', 'UPC011C', 'Type C', 3, 4.00, 11.50, 2.00, 0.40, 1, 'Enclosure Type C with vents');

-- ProductID 12: Plastic Enclosure no 3 - 2个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(12, 'ENC-012-A', 'UPC012A', 'Gray', 1, 2.00, 6.50, 1.50, 0.22, 1, 'Gray color'),
(12, 'ENC-012-B', 'UPC012B', 'Black', 2, 2.00, 6.50, 1.50, 0.22, 1, 'Black color');

-- ProductID 13: Plastic Enclosure no 4 - 3个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(13, 'ENC-013-A', 'UPC013A', 'IP54', 1, 3.00, 8.99, 1.80, 0.30, 1, 'IP54 protection rating'),
(13, 'ENC-013-B', 'UPC013B', 'IP65', 2, 4.50, 13.50, 2.00, 0.35, 1, 'IP65 protection rating'),
(13, 'ENC-013-C', 'UPC013C', 'IP67', 3, 6.00, 18.99, 2.50, 0.45, 1, 'IP67 protection rating');

-- ProductID 14: Plastic Enclosure no 5 - 2个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(14, 'ENC-014-A', 'UPC014A', 'With Lid', 1, 3.50, 10.50, 2.00, 0.35, 1, 'With transparent lid'),
(14, 'ENC-014-B', 'UPC014B', 'Without Lid', 2, 2.50, 7.50, 1.50, 0.28, 1, 'Without lid');

-- ProductID 15: Plastic Enclosure no 6 - 3个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(15, 'ENC-015-A', 'UPC015A', '80x50mm', 1, 1.50, 4.99, 1.00, 0.15, 1, 'Size 80x50x30mm'),
(15, 'ENC-015-B', 'UPC015B', '120x80mm', 2, 2.50, 7.99, 1.50, 0.25, 1, 'Size 120x80x40mm'),
(15, 'ENC-015-C', 'UPC015C', '200x120mm', 3, 4.00, 12.99, 2.00, 0.40, 1, 'Size 200x120x60mm');

-- ProductID 16: Plastic Enclosure no 7 - 2个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(16, 'ENC-016-A', 'UPC016A', 'Standard', 1, 2.00, 6.99, 1.50, 0.20, 1, 'Standard version'),
(16, 'ENC-016-B', 'UPC016B', 'With Mounting', 2, 2.80, 8.99, 1.80, 0.25, 1, 'With wall mounting brackets');

-- ProductID 17: TS-1001 - 3个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(17, 'TS-1001-A', 'UPC017A', '-20 to 100C', 1, 4.00, 12.99, 1.50, 0.20, 1, 'Range -20 to 100 Celsius'),
(17, 'TS-1001-B', 'UPC017B', '-40 to 150C', 2, 5.50, 17.50, 1.80, 0.25, 1, 'Range -40 to 150 Celsius'),
(17, 'TS-1001-C', 'UPC017C', '-50 to 200C', 3, 8.00, 26.99, 2.00, 0.30, 1, 'Range -50 to 200 Celsius');

-- ProductID 18: TS-1002 - 2个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(18, 'TS-1002-A', 'UPC018A', 'Analog Output', 1, 5.00, 15.99, 1.50, 0.22, 1, '4-20mA analog output'),
(18, 'TS-1002-B', 'UPC018B', 'Digital Output', 2, 6.00, 18.50, 1.80, 0.25, 1, 'Digital RS485 output');

-- ProductID 19: TS-1003 - 3个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(19, 'TS-1003-A', 'UPC019A', 'Basic', 1, 3.00, 9.99, 1.50, 0.20, 1, 'Basic temperature sensor'),
(19, 'TS-1003-B', 'UPC019B', 'With Display', 2, 5.00, 15.50, 1.80, 0.25, 1, 'With LCD display'),
(19, 'TS-1003-C', 'UPC019C', 'Wireless', 3, 12.00, 35.99, 2.00, 0.30, 1, 'Wireless transmission');

-- ProductID 21: USB to 485/422 - 2个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(21, 'USB-485-A', 'UPC021A', 'RS485 Only', 1, 4.00, 12.50, 1.50, 0.15, 1, 'RS485 interface only'),
(21, 'USB-485-B', 'UPC021B', 'RS485/422 Combo', 2, 6.00, 18.99, 1.80, 0.20, 1, 'RS485 and RS422 combo');

-- ProductID 22: USB to 232 - 3个型号
INSERT INTO `products_attributes` (`ProductID`, `SKU`, `UPC`, `AttributeName`, `AttributeOrder`, `AttributeCost`, `AttributePrice`, `ShippingPrice`, `ShippingWeight`, `Display`, `AttribtDescriptions`) VALUES
(22, 'USB-232-A', 'UPC022A', 'Standard', 1, 3.00, 9.99, 1.50, 0.15, 1, 'Standard USB to RS232'),
(22, 'USB-232-B', 'UPC022B', 'Isolated', 2, 8.00, 24.99, 1.80, 0.20, 1, 'Optical isolated version'),
(22, 'USB-232-C', 'UPC022C', 'Industrial', 3, 12.00, 38.50, 2.00, 0.30, 1, 'Industrial grade with ESD protection');
