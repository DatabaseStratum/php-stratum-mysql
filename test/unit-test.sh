#!/bin/bash -x

exec 2>&1

mysql -v -uroot -h127.0.0.1      < test/ddl/0010_create_database.sql
mysql -v -uroot -h127.0.0.1      < test/ddl/0020_create_user.sql
mysql -v -uroot -h127.0.0.1 test < test/ddl/0100_create_tables.sql

# Add unit tests for Oracle mode stored routines if supported by the RDBMS.
if [[ -L test/psql/oracle ]]; then
  rm test/psql/oracle
fi

mysql -utest -ptest -h127.0.0.1 test -e "
set SQL_MODE='ORACLE';
DELIMITER //
create function f0()
return int
as
begin
  return 0;
end;
//" 2>&1 > /dev/null
if [[ $? -eq 0 ]]; then
  ln -s ../oracle test/psql/oracle
fi

# Add unit tests for IPv4 column type if supported by the RDBMS.
if [[ -L test/psql/inet4 ]]; then
  rm test/psql/inet4
fi

mysql -utest -ptest -h127.0.0.1 test -e "create temporary table TST_IPV4(ip inet4)" 2>&1 > /dev/null
if [[ $? -eq 0 ]]; then
  ln -s ../inet4 test/psql/inet4

  mysql -v -uroot -h127.0.0.1 test < test/ddl/0100_create_tables_inet4.sql
fi

# Add unit tests for IPv6 column type if supported by the RDBMS.
if [[ -L test/psql/inet6 ]]; then
  rm test/psql/inet6
fi

mysql -utest -ptest -h127.0.0.1 test -e "create temporary table TST_IPV6(ip inet6)" 2>&1 > /dev/null
if [[ $? -eq 0 ]]; then
  ln -s ../inet6 test/psql/inet6

  mysql -v -uroot -h127.0.0.1 test < test/ddl/0100_create_tables_inet6.sql
fi

