

## Ceci est une API USER et AUTHENTIFICATION

Elle permet de gerer(consulter, creer et gerer les habilitaitons des users)

Creation de l'admin

php artisan create:user admin@example.com 123456 admin


php artisan user:create-admin admin 123456 --email=admin@admin.com

<!-- initialisation: migration, seeders et user admin -->
docker exec -it authentication-api php artisan migrate:fresh --seed && php artisan app:create-admin admin 123456 --email=admin@admin.com