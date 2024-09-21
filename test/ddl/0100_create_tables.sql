-- ---------------------------------------------------------------------------------------------------------------------
drop table if exists TST_FOO1;

create table TST_FOO1
(
  tst_int                int,
  tst_smallint           smallint,
  tst_tinyint            tinyint,
  tst_mediumint          mediumint,
  tst_bigint             bigint,
  tst_int_unsigned       int unsigned,
  tst_smallint_unsigned  smallint unsigned,
  tst_tinyint_unsigned   tinyint unsigned,
  tst_mediumint_unsigned mediumint unsigned,
  tst_bigint_unsigned    bigint unsigned,
  tst_decimal            decimal(10, 2),
  tst_decimal0           decimal(65),
  tst_float              float,
  tst_double             double,
  tst_bit                bit(8),
  tst_date               date,
  tst_datetime           datetime,
  tst_timestamp          timestamp,
  tst_time               time,
  tst_year               year,
  tst_char               char(10),
  tst_varchar            varchar(10),
  tst_binary             binary(10),
  tst_varbinary          varbinary(10),
  tst_tinyblob           tinyblob,
  tst_blob               blob,
  tst_mediumblob         mediumblob,
  tst_longblob           longblob,
  tst_tinytext           tinytext,
  tst_text               text,
  tst_mediumtext         mediumtext,
  tst_longtext           longtext,
  tst_enum               enum ('a','b','c'),
  tst_set                set ('a','b','c')
)
engine = myisam;

-- ---------------------------------------------------------------------------------------------------------------------
drop table if exists TST_FOO2;

create table TST_FOO2
(
  tst_c00 int,
  tst_c01 varchar(10),
  tst_c02 varchar(10),
  tst_c03 varchar(10),
  tst_c04 varchar(10)
)
engine = myisam;

insert into TST_FOO2( tst_c00
                    , tst_c01
                    , tst_c02
                    , tst_c03
                    , tst_c04 )
values ( 1
       , 'a'
       , 'b'
       , 'c1'
       , 'd' )
     , ( 2
       , 'a'
       , 'b'
       , 'c2'
       , 'd' )
     , ( 3
       , 'a'
       , 'b'
       , 'c3'
       , 'd' );

-- ---------------------------------------------------------------------------------------------------------------------
drop table if exists TST_TABLE;

create table TST_TABLE
(
  tst_c00 varchar(20),
  tst_c01 int(11),
  tst_c02 double,
  tst_c03 decimal(10, 5),
  tst_c04 datetime,
  t       int(11),
  s       int(11)
)
engine = myisam;

insert into TST_TABLE( tst_c00
                     , tst_c01
                     , tst_c02
                     , tst_c03
                     , tst_c04
                     , t
                     , s )
values ( 'Hello'
       , 1
       , '0.543'
       , '1.2345'
       , '2014-03-27 00:00:00'
       , '4444'
       , '1' )
     , ( 'World'
       , 3
       , '3E-05'
       , 0
       , '2014-03-28 00:00:00'
       , null
       , 1 );

-- ---------------------------------------------------------------------------------------------------------------------
drop table if exists TST_LABEL;

create table TST_LABEL
(
  tst_id    int unsigned not null auto_increment,
  tst_test  varchar(40),
  tst_label varchar(20),
  primary key (tst_id)
)
engine = myisam;

insert into TST_LABEL( tst_test
                     , tst_label )
values ( 'spam'
       , 'TST_ID_SPAM' )
     , ( 'eggs'
       , 'TST_ID_EGGS' )
     , ( 'bunny'
       , 'TST_ID_BUNNY' )
     , ( 'cat'
       , 'TST_ID_CAT' )
     , ( 'elephant'
       , 'TST_ID_ELEPHANT' );

-- ---------------------------------------------------------------------------------------------------------------------
drop table if exists TST_SORT;

create table TST_SORT
(
  tst_id         int unsigned not null auto_increment,
  tst_last_name  varchar(40) character set utf8mb4,
  tst_first_name varchar(40) character set utf8,
  tst_instrument varchar(40) character set ascii,
  primary key (tst_id)
)
engine = myisam;

insert into TST_SORT( tst_last_name
                    , tst_first_name
                    , tst_instrument )
values ( 'Morrison'
       , 'Jim'
       , 'vocals' )
     , ( 'Manzarek'
       , 'Ray'
       , 'keyboards' )
     , ( 'Krieger'
       , 'Robby'
       , 'guitar' )
     , ( 'Densmore'
       , 'John'
       , 'drums' );

-- ---------------------------------------------------------------------------------------------------------------------
