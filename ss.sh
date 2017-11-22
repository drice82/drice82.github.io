dpkg-reconfigure tzdata 
apt-get update
apt-get install python-pip git python-m2crypto supervisor vnstat lftp fail2ban wget 
pip install cymysql
cd /

#git clone -b manyuser https://github.com/drice82/shadowsocks.git
git clone https://github.com/drice82/shadowsocksr
cd /shadowsocksr
bash /shadowsocksr/initcfg.sh
bash /shadowsocksr/setup_cymysql.sh
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
