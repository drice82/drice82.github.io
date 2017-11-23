dpkg-reconfigure tzdata 
apt-get update
apt-get install python-pip git python-m2crypto supervisor vnstat lftp fail2ban wget 
pip install cymysql
cd /

#git clone -b manyuser https://github.com/drice82/shadowsocks.git
git clone https://github.com/drice82/shadowsocksr
cd /shadowsocksr
chmod +x *.sh
cp -n apiconfig.py userapiconfig.py
cp -n ssruser-config.json user-config.json
cp -n mysql.json usermysql.json
rm -rf CyMySQL
rm -rf cymysql
git clone https://github.com/nakagami/CyMySQL.git
mv CyMySQL/cymysql ./
rm -rf CyMySQL

echo "[program:shadowsocksr]" > /etc/supervisor/conf.d/shadowsocksr.conf
echo "command=python /shadowsocksr/server.py" >> /etc/supervisor/conf.d/shadowsocksr.conf
echo "autorestart=true" >> /etc/supervisor/conf.d/shadowsocksr.conf
echo "user=root" >> /etc/supervisor/conf.d/shadowsocksr.conf

nano /shadowsocksr/usermysql.json

service supervisor start 
supervisorctl reload
echo "ulimit -n 51200" >>/etc/profile
echo "ulimit -Sn 4096" >>/etc/profile
echo "ulimit -Hn 8192" >>/etc/profile
echo "ulimit -n 51200" >>/etc/default/supervisor
echo "ulimit -Sn 4096" >>/etc/default/supervisor
echo "ulimit -Hn 8192" >>/etc/default/supervisor
