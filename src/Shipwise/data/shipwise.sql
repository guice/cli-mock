
drop table order_products;
CREATE TABLE "order_products" (
  "order_id" varchar(40) NOT NULL,
  "product_id" varchar(40) NOT NULL,
  "quantity" integer NOT NULL);

CREATE UNIQUE INDEX "order_products_idx" ON order_products(order_id,product_id);

drop table "order";
CREATE TABLE "order" (
  "id" varchar(40) primary key NOT NULL,
  "ship_addr" varchar(40) NOT NULL,
  "warehouse_id" varchar(40),
  "latitude" double NOT NULL DEFAULT 0,
  "longitude" double NOT NULL DEFAULT 0);

drop table "product";
CREATE TABLE "product" (
  "id" varchar(40) primary key NOT NULL,
  "name" varchar(150) NOT NULL,
  "dimensions" varchar(40) NOT NULL,
  "weight" decimal(4,5) NOT NULL);
CREATE UNIQUE INDEX "product_name_idx" ON "product" ("name");

insert into `product` VALUES ('1', 'Ball Cap');
insert into `product` VALUES ('2', 'Sugar');
insert into `product` VALUES ('3', 'Dive Fins');
insert into `product` VALUES ('4', 'Mask');

drop table "warehouse";
CREATE TABLE "warehouse" (
  "id" varchar(40) primary key NOT NULL,
  "name" varchar(150) NOT NULL,
  "address" varchar(250) NOT NULL,
  "latitude" double NOT NULL DEFAULT 0,
  "longitude" double NOT NULL DEFAULT 0);
CREATE UNIQUE INDEX "warehouse_name_idx" ON "warehouse" ("name");

INSERT INTO warehouse (id, name, address, latitude, longitude) VALUES ('b750', 'San Jose', '95 Holger Way, San Jose, CA 95134', 37.4180068, -121.9565003);
INSERT INTO warehouse (id, name, address, latitude, longitude) VALUES ('aaeb', 'Dallas TX', '2417 N Haskell Ave, Dallas, TX 75204', 32.8038505, -96.7913092);
INSERT INTO warehouse (id, name, address, latitude, longitude) VALUES ('839d', 'Portland', '939 SW Morrison St, Portland, OR 97205', 45.5200689, -122.6817797);
INSERT INTO warehouse (id, name, address, latitude, longitude) VALUES ('91fc', 'Kansas City', '9040 N Skyview Ave, Kansas City, MO 64154', 39.2573007, -94.6552367);
INSERT INTO warehouse (id, name, address, latitude, longitude) VALUES ('ad6d', 'Miami', '3401 N Miami Ave, Miami, FL 33127', 25.8093515, -80.194191);

drop table "warehouse_products";
CREATE TABLE "warehouse_products" (
  "warehouse_id" integer NOT NULL,
  "product_id" integer NOT NULL,
  "stock" integer NOT NULL);

CREATE UNIQUE INDEX "warehouse_products_idx" ON warehouse_products(warehouse_id,product_id);
CREATE INDEX "warehouse_id_idx" ON "warehouse_products" ("warehouse_id");
CREATE INDEX "product_id_idx" ON "warehouse_products" ("product_id");

INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('b750', '1', 3);
INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('b750', '2', 1);
INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('b750', '3', 5);
INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('b750', '4', 3);

INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('aaeb', '1', 3);
INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('aaeb', '2', 1);
INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('aaeb', '3', 5);

INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('839d', '1', 3);
INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('839d', '2', 11);
INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('839d', '3', 5);
INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('839d', '4', 31);


INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('ad6d', '1', 3);
INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('ad6d', '2', 11);

INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('91fc', '3', 5);
INSERT INTO warehouse_products (warehouse_id, product_id, stock) VALUES ('91fc', '4', 31);

-- Square root of the difference
SELECT * AS distance FROM items ORDER BY ((location_lat-lat)*(location_lat-lat)) + ((location_lng - lng)*(location_lng - lng)) ASC
-- Add where ID in () list for only warehouses with products in stock

SELECT warehouse_id FROM warehouse_products wp
  JOIN order_products as op ON wp.product_id = op.product_id AND op.quantity >= wp.stock
WHERE
  op.order_id = 'order id';


SELECT * FROM warehouse
WHERE warehouse_id IN ('')
ORDER BY (('my lat'-latitude)*('my lat'-latitude)) + (('my lng' - longitude)*('my lng' - longitude)) ASC;
