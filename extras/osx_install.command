#!/bin/sh
BASE=`dirname $0`
HTDOCS=/Applications/XAMPP/htdocs
BASEDIR=$HTDOCS/AgilityContest-master
CONFDIR=$HTDOCS/AgilityContest-master/agility/server/auth
EXTRAS=$HTDOCS/AgilityContest-master/extras

# request root permissions before continue install
if [ "$USER" != "root" ]; then
    echo "Password required to continue as user root"
    sudo $0
    exit 1
fi

if [ ! -d /Applications/XAMPP ]; then
    echo "XAMPP for Mac-OSX is not installed"
    echo "Download and install it from https://www.apachefriends.org/xampp-files/5.6.28/xampp-osx-5.6.28-1-installer.dmg"
    exit 1
fi

echo "Prepare..."
# curl https://codeload.github.com/jonsito/AgilityContest/zip/master -o /tmp/AgilityContest-master.zip
rm -f /tmp/registration.info
rm -f /tmp/config.ini
echo ""

echo "Unziping into $BASEDIR ... "
# make a backup copy of configuration and license
[ -f $CONFDIR/registration.info ] && cp $CONFDIR/registration.info /tmp
[ -f $CONFDIR/config.ini ] && cp $CONFDIR/config.ini /tmp

# create a backup of old application
rm -rf $BASEDIR.old
[ -d $BASEDIR ] && mv $BASEDIR $BASEDIR.old

# and unzip files
cd $HTDOCS;
unzip -q $BASE/AgilityContest-master.zip
echo ""

echo "Configuring MySQL... "
sed -i -e '/.*lower_case_table_names.*/d' /Applications/XAMPP/xamppfiles/etc/my.cnf
sed -i -e '/\[mysqld\]/a lower_case_table_names = 1' /Applications/XAMPP/xamppfiles/etc/my.cnf
echo ""

echo "Adding AgilityContest apache configuration file... "
cp $EXTRAS/AgilityContest_osx.conf /Applications/XAMPP/etc/extra
sed -i -e '/.*AgilityContest.*/d' /Applications/XAMPP/etc/httpd.conf
echo 'Include "/Applications/XAMPP/etc/extra/AgilityContest_osx.conf"' >> /Applications/XAMPP/etc/httpd.conf
echo ""

echo "Fixing permissions... "
cd $HTDOCS
chown -R daemon AgilityContest-master
chgrp -R daemon AgilityContest-master
xattr -dr com.apple.quarantine AgilityContest-master
echo ""

echo "Starting MySQL database server"
/Applications/XAMPP/bin/mysql.server restart
echo ""

echo "Creating and populating AgilityContest database"
cat <<_EOF | /Applications/XAMPP/bin/mysql -u root
DROP DATABASE IF EXISTS agility;
CREATE DATABASE agility;
USE agility;
SOURCE /Applications/XAMPP/htdocs/AgilityContest-master/extras/agility.sql;
SOURCE /Applications/XAMPP/htdocs/AgilityContest-master/extras/users.sql;
_EOF
echo ""

echo "Restoring license and configuration data"
[ -f /tmp/registration.info ] && cp /tmp/registration.info $CONFIG/registration.info
[ -f /tmp/config.ini ] && cp /tmp/config.ini $CONFIG/config.ini
echo ""

echo "Instalacion completada"
echo "Arrancar la aplicación S/[n]?"
read a
case $a in
    [SsYy]* )
        /Applications/XAMPP/xamppfiles/xampp restart
        open -a Safari https://localhost/agility/console
    ;;
esac
