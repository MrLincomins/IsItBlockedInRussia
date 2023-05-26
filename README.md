# IsItBlockedinRkn
git clone https://github.com/MrLincomins/IsItBlockedInRussia./rkn

cd ./rkn

docker compose build

docker compose up -d

cat zapret_blocked.sql | docker exec -i rkn-mysql mysql -uzapret  -pzapret_pwd zapret

docker exec -i rkn-php composer update